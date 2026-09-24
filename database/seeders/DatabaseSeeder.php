<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo používateľ, s ktorým sa dá hneď prihlásiť
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'test@example.com',
        ]);

        // 5 tagov patriacich demo používateľovi
        $tags = collect(['práca', 'škola', 'domov', 'nákupy', 'urgentné'])
            ->map(fn(string $name) => $user->tags()->create(['name' => $name]));

        // 20 úloh a každej priradíme 0 až 3 náhodné tagy
        Task::factory(20)
            ->for($user)
            ->create()
            ->each(function (Task $task) use ($tags) {
                $task->tags()->attach($tags->random(rand(0, 3))->pluck('id'));
            });

        // Druhý používateľ s pár úlohami, na overenie, že si úlohy navzájom nevidia
        User::factory()
            ->has(Task::factory()->count(3))
            ->create(['email' => 'other@example.com']);
    }
}
