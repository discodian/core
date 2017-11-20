<?php

/*
 * This file is part of the Discodian bot toolkit.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://discodian.com
 * @see https://github.com/discodian
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersCreate extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('username');
            $table->unsignedInteger('discriminator');
            $table->boolean('verified');
            $table->string('email');
            $table->string('avatar');
            $table->boolean('bot');

            $table->index(['username', 'discriminator']);
        });
    }

    public function down()
    {
        Schema::drop('users');
    }
}
