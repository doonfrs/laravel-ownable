<?php

namespace Trinavo\Ownable\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Trinavo\Ownable\Tests\TestCase;
use Trinavo\Ownable\Models\Ownership;
use Trinavo\Ownable\Traits\Ownable;
use Mockery;
use PhpParser\Node\Arg;

class Article extends Model
{
    use Ownable;

    protected $guarded = [];

}

class TestUser extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}

class OwnableTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        // Create articles table
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        //run the migration
        $this->artisan('migrate');

    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('articles');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_add_current_user_as_owner()
    {
        // Create users
        $user1 = TestUser::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
        ]);


        Auth::shouldReceive('user')
            ->andReturn($user1);
        
        $article = new Article();
        $article->title = 'Test Article';
        $article->content = 'Test Content';
        $article->save();

        $this->assertTrue($article->isOwnedBy($user1));
    }

    public function test_can_scope_to_models_owned_by_a_user()
    {
        // Create users
        $user1 = TestUser::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
        ]);

        $user2 = TestUser::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
        ]);

        // Create articles with no ownership - bypassing the boot method
        $article1 = new Article();
        $article1->title = 'Article 1';
        $article1->content = 'Content for article 1';
        $article1->save();

        $article2 = new Article();
        $article2->title = 'Article 2';
        $article2->content = 'Content for article 2';
        $article2->save();

        $article3 = new Article();
        $article3->title = 'Article 3';
        $article3->content = 'Content for article 3';
        $article3->save();

        // Manually add ownership
        $article1->addOwner($user1);
        $article2->addOwner($user1);
        $article3->addOwner($user2);

        
        // Test the scopeOwnedBy method with explicit user parameters
        $ownedArticles = Article::ownedBy($user1)->get();
        // Assert
        $this->assertCount(2, $ownedArticles);
        $this->assertTrue($ownedArticles->contains($article1));
        $this->assertTrue($ownedArticles->contains($article2));
        $this->assertFalse($ownedArticles->contains($article3));

        // Test with second user
        $user2Articles = Article::ownedBy($user2)->get();
        $this->assertCount(1, $user2Articles);
        $this->assertTrue($user2Articles->contains($article3));

        // Test with Auth facade
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user1);

        $authFacadeArticles = Article::mine()->get();
        $this->assertCount(2, $authFacadeArticles);
    }

    public function test_addowner_prevents_duplicates()
    {
        // Create user
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create article
        $article = new Article();
        $article->title = 'Test Article';
        $article->content = 'Test Content';
        $article->saveQuietly();

        // Add owner for the first time
        $article->addOwner($user);
        
        // Try to add the same owner again
        $article->addOwner($user);
        
        // Check that only one ownership record exists
        $ownerships = $article->getOwners();
        $this->assertCount(1, $ownerships);
    }
    
    public function test_delete_removes_current_user_as_owner()
    {
        // Create user
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Create article and add user as owner
        $article = new Article();
        $article->title = 'Test Article';
        $article->content = 'Test Content';
        $article->saveQuietly();
        $article->addOwner($user);
        
        // Verify user is owner
        $this->assertTrue($article->isOwnedBy($user));
        
        // Delete article
        $article->delete();
        
        // Verify ownership records were removed
        $ownerships = Ownership::where('model_class', $article->getMorphClass())
            ->where('record_id', $article->id)
            ->count();
            
        $this->assertEquals(0, $ownerships);
    }
}
