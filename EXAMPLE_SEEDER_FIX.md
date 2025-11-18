# Example Seeder Fix

This document shows a complete example of how to fix an incorrect answer in a seeder and add the required tags.

## Hypothetical Error Example

### Original Seeder (with error)
```php
<?php
namespace Database\Seeders\V1\Tenses\Present;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ExampleSeeder extends Seeder
{
    public function run()
    {
        $categoryId = 2; // Present
        $sourceId = Source::firstOrCreate([
            'name' => 'Example Test'
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'present_simple']);

        $data = [
            [
                'question' => 'She {a1} to work every day.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'go',  // ❌ INCORRECT - should be 'goes' for third person singular
                        'verb_hint' => 'go',
                    ]
                ],
                'options' => ['go', 'goes', 'going'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $d['uuid']        = $uuid;
            $d['category_id'] = $categoryId;
            $d['tag_ids']     = [$themeTag->id];

            $items[] = $d;
        }

        $service->seed($items);
    }
}
```

### Fixed Seeder (with correction tags)
```php
<?php
namespace Database\Seeders\V1\Tenses\Present;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ExampleSeeder extends Seeder
{
    public function run()
    {
        $categoryId = 2; // Present
        $sourceId = Source::firstOrCreate([
            'name' => 'Example Test'
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'present_simple']);
        
        // Create correction tags
        $fixedTag = Tag::firstOrCreate(
            ['name' => 'fixed'],
            ['category' => 'Review Status']
        );
        
        $correctionTag = Tag::firstOrCreate(
            ['name' => 'go -> goes'],  // неправильна відповідь -> правильна відповідь
            ['category' => 'Answer Corrections']
        );

        $data = [
            [
                'question' => 'She {a1} to work every day.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'goes',  // ✅ FIXED - changed from 'go' to 'goes'
                        'verb_hint' => 'go',
                    ]
                ],
                'options' => ['go', 'goes', 'going'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $d['uuid']        = $uuid;
            $d['category_id'] = $categoryId;
            // Add correction tags to the question
            $d['tag_ids']     = [$themeTag->id, $fixedTag->id, $correctionTag->id];

            $items[] = $d;
        }

        $service->seed($items);
    }
}
```

## Changes Made

1. **Fixed the answer**: Changed `'answer' => 'go'` to `'answer' => 'goes'`
   - Reason: In Present Simple, third person singular (she/he/it) requires verb+s/es
   
2. **Added 'fixed' tag**: 
   ```php
   $fixedTag = Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Review Status']);
   ```
   
3. **Added correction tag** (incorrect -> correct):
   ```php
   $correctionTag = Tag::firstOrCreate(
       ['name' => 'go -> goes'],
       ['category' => 'Answer Corrections']
   );
   ```
   
4. **Applied tags to question**:
   ```php
   $d['tag_ids'] = [$themeTag->id, $fixedTag->id, $correctionTag->id];
   ```

## Tag Format

The correction tag follows the format requested in Ukrainian:
- `"неправильна відповідь -> правильна відповідь"`  
- Example: `"go -> goes"`, `"was -> were"`, `"don't -> doesn't"`

## Testing the Fix

After making changes:

```bash
# Run the specific seeder
php artisan db:seed --class="Database\Seeders\V1\Tenses\Present\ExampleSeeder"

# Verify tags were created
php artisan tinker
>>> Tag::where('name', 'fixed')->first()
>>> Tag::where('name', 'go -> goes')->first()

# Verify question has tags
>>> Question::where('question', 'like', '%to work every day%')->with('tags')->first()
```

## Common Mistakes to Fix

### 1. Subject-Verb Agreement
```php
// Wrong: 'He go to school'
'answer' => 'go'

// Right: 'He goes to school'  
'answer' => 'goes'
Tag: 'go -> goes'
```

### 2. Verb Tense Errors
```php
// Wrong: 'She go yesterday' (past context)
'answer' => 'go'

// Right: 'She went yesterday'
'answer' => 'went'
Tag: 'go -> went'
```

### 3. Auxiliary Verb Errors
```php
// Wrong: 'He don't like it'
'answer' => "don't"

// Right: 'He doesn't like it'
'answer' => "doesn't"
Tag: "don't -> doesn't"
```

### 4. Article Errors
```php
// Wrong: 'This is the apple' (indefinite context)
'answer' => 'the'

// Right: 'This is an apple'
'answer' => 'an'
Tag: 'the -> an'
```

## Multiple Fixes in Same Seeder

If multiple questions in the same seeder have errors, create individual correction tags for each:

```php
$fixedTag = Tag::firstOrCreate(['name' => 'fixed'], ['category' => 'Review Status']);

// Individual correction tags
$correction1 = Tag::firstOrCreate(['name' => 'go -> goes'], ['category' => 'Answer Corrections']);
$correction2 = Tag::firstOrCreate(['name' => 'was -> were'], ['category' => 'Answer Corrections']);
$correction3 = Tag::firstOrCreate(['name' => 'don\'t -> doesn\'t'], ['category' => 'Answer Corrections']);

// Apply appropriate tags to each question
$question1['tag_ids'] = [$themeTag->id, $fixedTag->id, $correction1->id];
$question2['tag_ids'] = [$themeTag->id, $fixedTag->id, $correction2->id];
$question3['tag_ids'] = [$themeTag->id, $fixedTag->id, $correction3->id];
```

## Documentation

For each fix, document:
1. File path
2. Line number(s) changed
3. Incorrect answer
4. Correct answer
5. Reason for change
6. Tags added

Example commit message:
```
Fix: Correct verb form in ExampleSeeder

- Changed 'go' to 'goes' for third person singular
- Added 'fixed' tag and 'go -> goes' correction tag
- File: database/seeders/V1/Tenses/Present/ExampleSeeder.php
```
