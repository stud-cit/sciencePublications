<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->nullable(); // guid кабінету
            $table->string('name'); // ім'я
            $table->string('date_bth')->nullable(); // дата народження
            $table->string('job')->nullable(); // місце роботи
            $table->string('faculty_code')->nullable(); // код факультету
            $table->string('department_code')->nullable(); // код кафедри
            $table->string('country')->default("Україна"); // країна
            $table->integer('h_index')->nullable(); // Індекс Гірша БД WoS
            $table->integer('scopus_autor_id')->nullable(); // Індекс Гірша БД Scopus
            $table->string('scopus_researcher_id')->nullable(); // Research ID
            $table->string('orcid')->nullable(); // ORCID
            $table->string('academic_code')->nullable(); // академічна група
            $table->foreignId('roles_id')->default(1); // роль
            $table->foreignId('categ_1')->nullable(); // categ_1 кабінету
            $table->foreignId('categ_2')->nullable(); // categ_2 кабінету
            $table->string('token')->nullable(); // token кабінету
            $table->boolean('forbes_fortune')->nullable(); // Входить до списків Forbes та Fortune
            $table->boolean('five_publications')->default(0); // 5 або більше публікацій в Scopus та/або WoS
            $table->integer('without_self_citations_wos')->nullable(); // Без самоцитувань WoS
            $table->integer('without_self_citations_scopus')->nullable(); // Без самоцитувань Scopus
            $table->integer('add_user_id')->nullable(); // Користувач що додав атора
            $table->text('test_data')->nullable();
            $table->boolean('custom_divisions')->default(0);
            $table->timestamps();
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->index('roles_id');
            $table->foreign('roles_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authors');
    }
}
