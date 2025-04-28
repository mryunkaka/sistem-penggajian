<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Table: units
        Schema::create('units', function (Blueprint $table) {
            $table->id();  // Mengganti uuid dengan auto-increment id
            $table->string('nama_unit');
            $table->text('alamat_unit')->nullable();
            $table->string('no_hp_unit')->nullable();
            $table->string('logo_unit')->nullable();
            $table->timestamps();
        });

        // Table: users (gabungan user + karyawan)
        Schema::create('users', function (Blueprint $table) {
            $table->id();  // Mengganti uuid dengan auto-increment id
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('agama')->nullable();
            $table->string('status_perkawinan')->nullable();
            $table->string('nik')->nullable();
            $table->string('npwp')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('role')->default('karyawan');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');  // Mengganti foreignUuid menjadi foreignId
            $table->date('tanggal_bergabung')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Table: absensi
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();  // Mengganti id menjadi auto-increment
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');  // Mengganti foreignUuid menjadi foreignId
            $table->bigInteger('shift')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status')->nullable();
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->integer('total_menit_terlambat')->default(0);
            $table->bigInteger('jumlah_potongan')->default(0);
            $table->string('keterangan_potongan')->nullable();
            $table->bigInteger('lembur')->default(0);
            $table->string('keterangan_lembur')->nullable();
            $table->bigInteger('potongan_kehadiran')->default(0);
            $table->string('keterangan_kehadiran')->nullable();
            $table->bigInteger('potongan_ijin')->default(0);
            $table->string('keterangan_ijin')->nullable();
            $table->bigInteger('potongan_khusus')->default(0);
            $table->string('keterangan_khusus')->nullable();
            $table->timestamps();
        });

        // Table: penggajian
        Schema::create('penggajian', function (Blueprint $table) {
            $table->id();  // Mengganti id menjadi auto-increment
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');  // Mengganti foreignUuid menjadi foreignId
            $table->date('tanggal_awal_gaji');
            $table->date('tanggal_akhir_gaji');
            $table->bigInteger('gaji_bersih')->default(0);
            $table->bigInteger('gaji_pokok')->default(0);
            $table->bigInteger('gaji_kotor')->default(0);
            $table->bigInteger('tunjangan_bbm')->default(0);
            $table->bigInteger('tunjangan_makan')->default(0);
            $table->bigInteger('tunjangan_jabatan')->default(0);
            $table->bigInteger('tunjangan_kehadiran')->default(0);
            $table->bigInteger('tunjangan_lainnya')->default(0);
            $table->bigInteger('lembur')->default(0);
            $table->bigInteger('potongan_kehadiran')->default(0);
            $table->bigInteger('potongan_ijin')->default(0);
            $table->bigInteger('potongan_terlambat')->default(0);
            $table->bigInteger('pot_bpjs_jht')->default(0);
            $table->bigInteger('pot_bpjs_kes')->default(0);
            $table->bigInteger('total_potongan')->default(0);
            $table->timestamps();
        });

        // Table: master_gaji
        Schema::create('master_gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke tabel users

            // Komponen Gaji
            $table->decimal('gaji_pokok', 12, 2)->default(0);
            $table->decimal('tunjangan_bbm', 12, 2)->default(0);
            $table->decimal('tunjangan_makan', 12, 2)->default(0);
            $table->decimal('tunjangan_jabatan', 12, 2)->default(0);
            $table->decimal('tunjangan_kehadiran', 12, 2)->default(0);
            $table->decimal('tunjangan_lainnya', 12, 2)->default(0);

            // Komponen Potongan
            $table->decimal('potongan_terlambat', 12, 2)->default(0);
            $table->decimal('pot_bpjs_jht', 12, 2)->default(0);
            $table->decimal('pot_bpjs_kes', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_gaji');
        Schema::dropIfExists('penggajian');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('users');
        Schema::dropIfExists('units');
    }
};
