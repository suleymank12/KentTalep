<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Tickets\CreateTicket;
use App\Enums\Role;
use App\Enums\TicketMediaType;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketStateMachine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

/**
 * Yalnızca yerel geliştirme. ~50 gerçekçi Türkçe talep üretir. Durumlar
 * TABLOYA ELLE yazılmaz: her talep pending oluşturulup hedef duruma
 * TicketStateMachine üzerinden geçirilir; böylece loglar gerçek ve tutarlıdır.
 */
class TicketSeeder extends Seeder
{
    /** Ankara Kızılay merkezi [enlem, boylam]. */
    private const DEMO_CENTER = [39.925, 32.854];

    /**
     * @var list<array{status: TicketStatus, count: int}>
     */
    private const DISTRIBUTION = [
        ['status' => TicketStatus::Pending, 'count' => 15],
        ['status' => TicketStatus::Assigned, 'count' => 8],
        ['status' => TicketStatus::InProgress, 'count' => 8],
        ['status' => TicketStatus::Resolved, 'count' => 10],
        ['status' => TicketStatus::Closed, 'count' => 5],
        ['status' => TicketStatus::Cancelled, 'count' => 3],
        ['status' => TicketStatus::Rejected, 'count' => 3],
    ];

    /**
     * @var list<array{0: string, 1: string}>
     */
    private const CONTENT = [
        ['Kaldırımda derin çukur var', 'Mahalle girişindeki kaldırımda yayaların düşmesine yol açan derin bir çukur oluştu. Acil onarım gerekiyor.'],
        ['Sokak lambası günlerdir yanmıyor', 'Akşamları sokağımız tamamen karanlık kalıyor, güvenlik açısından tehlikeli. Lambanın tamiri isteniyor.'],
        ['Çöp konteyneri taşmış durumda', 'Konteyner günlerdir boşaltılmadı, etrafa kötü koku yayılıyor ve sokak hayvanları çöpleri dağıtıyor.'],
        ['Yol ortasında büyük çukur', 'Ana cadde üzerinde araç lastiklerine zarar veren büyük bir çukur var. Trafik akışını da engelliyor.'],
        ['Parktaki banklar kırılmış', 'Çocuk parkındaki oturma bankları kırık, oturulamıyor. Ailelerin kullanımı için onarılması gerekiyor.'],
        ['Su borusu patlamış, cadde su altında', 'Sabah patlayan su borusu nedeniyle cadde göle döndü, yayalar geçemiyor.'],
        ['Trafik ışığı arızalı', 'Kavşaktaki trafik ışığı sürekli sarı yanıp sönüyor, kazaya davetiye çıkarıyor.'],
        ['Başıboş köpekler tehlike saçıyor', 'Okul çevresinde başıboş köpek sürüsü öğrencilere korku veriyor, önlem alınması gerekiyor.'],
        ['Kaldırım işgali var', 'Esnaf kaldırıma masa sandalye koyarak yaya geçişini engelliyor, engelliler geçemiyor.'],
        ['Asfalt tamamen bozulmuş', 'Sokağımızın asfaltı yağmurlarla çökmüş, çamur içinde yürünüyor.'],
        ['Yağmur suyu gideri tıkalı', 'Yağış sonrası su tahliye edilemiyor, bodrum katlarını su basıyor.'],
        ['Ağaç dalları elektrik teline değiyor', 'Budanmayan ağaç dalları elektrik hattına temas ediyor, kısa devre riski var.'],
    ];

    public function run(): void
    {
        $create = app(CreateTicket::class);
        $machine = app(TicketStateMachine::class);

        $manager = User::where('email', 'manager@kenttalep.test')->firstOrFail();
        $staff = User::whereHas('roles', fn (Builder $q) => $q->where('name', Role::Staff->value))->get();

        $citizens = User::factory(12)->create();
        $citizens->each(fn (User $citizen) => $citizen->assignRole(Role::Citizen->value));

        $categories = Category::all();

        $targets = [];
        foreach (self::DISTRIBUTION as $bucket) {
            $targets = array_merge($targets, array_fill(0, $bucket['count'], $bucket['status']));
        }
        shuffle($targets);

        foreach ($targets as $target) {
            $owner = $citizens->random();
            $staffMember = $staff->random();
            $category = $categories->random();
            $content = self::CONTENT[array_rand(self::CONTENT)];
            [$latitude, $longitude] = $this->randomLocation();

            $ticket = $create->handle(
                $owner,
                $content[0],
                $content[1],
                (int) $category->id,
                $latitude,
                $longitude,
                fake()->streetAddress(),
            );

            $this->drive($machine, $ticket, $target, $manager, $staffMember, $owner);
        }
    }

    private function drive(
        TicketStateMachine $machine,
        Ticket $ticket,
        TicketStatus $target,
        User $manager,
        User $staff,
        User $owner,
    ): void {
        if ($target === TicketStatus::Pending) {
            return;
        }

        if ($target === TicketStatus::Cancelled) {
            $machine->transition($ticket, TicketStatus::Cancelled, $owner, 'Vatandaş talebini geri çekti.');

            return;
        }

        if ($target === TicketStatus::Rejected) {
            $machine->transition($ticket, TicketStatus::Rejected, $manager, 'Talep belediye görev alanı dışında.');

            return;
        }

        $machine->transition($ticket, TicketStatus::Assigned, $manager, null, ['assigned_to' => $staff->getKey()]);
        if ($target === TicketStatus::Assigned) {
            return;
        }

        $machine->transition($ticket, TicketStatus::InProgress, $staff);
        if ($target === TicketStatus::InProgress) {
            return;
        }

        // resolve şartı: en az bir "sonrası" medya. Seeder dosyasız placeholder ekler.
        $this->seedAfterMedia($ticket, $staff);
        $machine->transition($ticket, TicketStatus::Resolved, $staff, 'Saha ekibi sorunu giderdi.');
        if ($target === TicketStatus::Resolved) {
            return;
        }

        $machine->transition($ticket, TicketStatus::Closed, $manager);
    }

    private function seedAfterMedia(Ticket $ticket, User $staff): void
    {
        $ticket->media()->create([
            'uploaded_by' => $staff->getKey(),
            'type' => TicketMediaType::After->value,
            'disk' => (string) config('kenttalep.media_disk'),
            'path' => 'seed/placeholder.jpg',
            'thumb_path' => 'seed/placeholder_thumb.jpg',
            'original_name' => 'placeholder.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 0,
            'width' => 0,
            'height' => 0,
        ]);
    }

    /**
     * DEMO_CENTER etrafında ~8 km yarıçapta rastgele konum.
     *
     * @return array{0: float, 1: float}
     */
    private function randomLocation(): array
    {
        $distanceKm = sqrt(fake()->randomFloat(6, 0, 1)) * 8.0;
        $angle = fake()->randomFloat(6, 0, 1) * 2 * M_PI;

        $latitude = self::DEMO_CENTER[0] + ($distanceKm / 111.0) * cos($angle);
        $longitude = self::DEMO_CENTER[1] + ($distanceKm / (111.0 * cos(deg2rad(self::DEMO_CENTER[0])))) * sin($angle);

        return [$latitude, $longitude];
    }
}
