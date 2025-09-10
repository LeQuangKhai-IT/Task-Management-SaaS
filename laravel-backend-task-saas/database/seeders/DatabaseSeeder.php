<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Board;
use App\Models\Card;
use App\Models\Checklist;
use App\Models\Comment;
use App\Models\Label;
use App\Models\TaskList;
use App\Models\Workspace;
use App\Models\User;
use App\Models\Attachment;
use App\Models\Activity;
use App\Models\Checklist_item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 10 random users and 1 admin
        User::factory()->count(10)->create();
        $admin = User::create([
            'email' => 'admin@gmail.com',
            'fullname' => 'i am admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create 5 workspaces
        Workspace::factory()->count(5)->create()->each(function ($org) use ($admin) {
            // Add owner as admin of workspace
            $org->users()->syncWithoutDetaching([
                $org->owner_id => ['role' => 'admin'],
                $admin->id => ['role' => 'admin'],
            ]);

            // Create 3 boards for each workspace
            Board::factory()->count(3)->create([
                'workspace_id' => $org->id,
            ])->each(function ($board) use ($admin) {
                // Add board users (owner and admin)
                $board->users()->syncWithoutDetaching([
                    $board->workspace->owner_id => ['role' => 'admin'],
                    $admin->id => ['role' => 'admin'],
                    User::inRandomOrder()->first()->id => ['role' => 'member'],
                ]);

                // Create 3 labels for the board
                $labels = Label::factory()->count(3)->create([
                    'board_id' => $board->id,
                ]);

                // Create 3 lists for each board
                TaskList::factory()->count(3)->create([
                    'board_id' => $board->id,
                ])->each(function ($list) use ($labels, $admin) {
                    // Create 5 cards for each list
                    Card::factory()->count(5)->create([
                        'list_id' => $list->id,
                    ])->each(function ($card) use ($labels, $admin) {
                        // Add card user
                        $card->users()->syncWithoutDetaching([
                            $admin->id,
                            User::inRandomOrder()->first()->id,
                        ]);

                        // Assign 1-2 random labels to the card
                        $card->labels()->sync($labels->random(rand(1, 2))->pluck('id'));

                        // Create a checklist for the card
                        Checklist::factory()->create([
                            'card_id' => $card->id,
                        ])->each(function ($checklist) {
                            // Create 3 checklist items
                            Checklist_item::factory()->count(3)->create([
                                'checklist_id' => $checklist->id,
                            ]);
                        });

                        // Create 1 attachment
                        Attachment::factory()->create([
                            'card_id' => $card->id,
                            'uploaded_by' => User::inRandomOrder()->first()->id,
                        ]);

                        // Create 1 comment
                        Comment::factory()->create([
                            'card_id' => $card->id,
                            'user_id' => User::inRandomOrder()->first()->id,
                        ]);

                        // Create 1 activity
                        Activity::factory()->create([
                            'board_id' => $card->list->board_id,
                            'user_id' => User::inRandomOrder()->first()->id,
                        ]);
                    });
                });
            });
        });
    }
}
