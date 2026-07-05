-- KentTalep — PostgreSQL başlangıç scripti
-- Bu dosya konteynere /docker-entrypoint-initdb.d/zz-kenttalep.sql olarak
-- mount edilir. "zz" öneki, imajın kendi PostGIS scriptinden SONRA çalışmasını
-- garanti eder. Yalnızca veritabanı ilk kez oluşturulurken çalışır.

-- Test veritabanını oluştur (ana veritabanı POSTGRES_DB env'i ile açılır).
CREATE DATABASE kenttalep_test;

-- Ana veritabanına geç ve PostGIS eklentisini kur.
\connect kenttalep
CREATE EXTENSION IF NOT EXISTS postgis;

-- Test veritabanına geç ve PostGIS eklentisini kur.
\connect kenttalep_test
CREATE EXTENSION IF NOT EXISTS postgis;
