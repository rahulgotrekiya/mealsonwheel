<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');
        $merchants = User::whereIn('email', [
            'pawsome@mealsonwheels.test',
            'whiskers@mealsonwheels.test',
            'featherfur@mealsonwheels.test',
        ])->pluck('id', 'email');

        // Each merchant supplies a distinct part of the catalog.
        $suppliers = [
            'dog' => $merchants['pawsome@mealsonwheels.test'],
            'cat' => $merchants['whiskers@mealsonwheels.test'],
            'bird' => $merchants['featherfur@mealsonwheels.test'],
            'small-animals' => $merchants['featherfur@mealsonwheels.test'],
        ];

        foreach ($this->products() as $data) {
            $images = $data['images'];
            unset($data['images']);

            $categoryId = $categories[$data['category']];
            $sellerId = $suppliers[$data['category']];
            unset($data['category']);

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    ...$data,
                    'slug' => Str::slug($data['name']),
                    'category_id' => $categoryId,
                    'seller_id' => $sellerId,
                    'status' => ProductStatus::Approved,
                ]
            );

            $product->images()->delete();

            foreach ($images as $path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => "images/products/{$path}",
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        return [
            // Dog
            [
                'category' => 'dog',
                'name' => 'Woof Treats Organic Dog Snacks 150g',
                'price' => 1000.00,
                'stock' => 48,
                'description' => '<p>Bite-sized organic snacks made for training, rewarding, or simply spoiling your dog. Every batch uses certified organic ingredients and nothing else.</p><ul><li>Certified organic ingredients throughout</li><li>Grain-free recipe for sensitive stomachs</li><li>Small pieces sized for repeat training rewards</li><li>No artificial colours, flavours or preservatives</li><li>Suitable for all breeds, ages and sizes</li></ul>',
                'additional_info' => '<p>Feed as a treat alongside your dog\'s regular meals, adjusting the amount for size, age and activity level. Reseal the bag after opening and keep it somewhere cool and dry. Fresh drinking water should always be available.</p>',
                'images' => ['woof-treats-organic-dog-snacks.jpg'],
            ],
            [
                'category' => 'dog',
                'name' => 'Paw Pupper All Natural Dog Treats 150g',
                'price' => 349.00,
                'stock' => 120,
                'description' => '<p>Everyday treats built from simple, recognisable ingredients, in flavour pairings dogs actually finish.</p><ul><li>Chicken &amp; potato or peanut butter &amp; banana</li><li>Natural ingredients with no fillers</li><li>Added vitamins for active dogs</li><li>Soft enough for puppies and senior dogs</li><li>Resealable pouch keeps them fresh</li></ul>',
                'additional_info' => '<p>Best used as a reward during training or as an occasional snack. Treats should stay under ten percent of your dog\'s daily intake. Store sealed, away from direct sunlight.</p>',
                'images' => ['paw-pupper-all-natural-dog-treats.jpg'],
            ],
            [
                'category' => 'dog',
                'name' => 'Acana Free Run Duck Dry Dog Food 6kg',
                'price' => 769.00,
                'stock' => 22,
                'description' => '<p>A single-protein dry food for dogs that react badly to richer recipes. Half free-run duck, half fruit, vegetables and botanicals.</p><ul><li>One easily digestible protein source</li><li>Grain-free, with no potato or tapioca</li><li>50% duck, 50% fruit, vegetables and botanicals</li><li>Formulated for all breeds and life stages</li><li>Meets AAFCO nutrient profiles</li></ul>',
                'additional_info' => '<p>Introduce over seven to ten days, mixing an increasing share into the current food. Daily amounts depend on weight and activity. Speak to your vet before changing the diet of a dog with an existing condition.</p>',
                'images' => ['acana-free-run-duck-dry-dog-food.jpg'],
            ],
            [
                'category' => 'dog',
                'name' => 'Gobble Gently Cooked Chicken Liver with Rice Wet Dog Food 300g',
                'price' => 840.00,
                'stock' => 4,
                'description' => '<p>Gently cooked rather than retorted, so the chicken liver keeps its texture and smell. Useful for fussy eaters and dogs recovering from illness.</p><ul><li>Chicken liver and rice, slow cooked</li><li>High moisture content supports hydration</li><li>Strong aroma tempts reluctant eaters</li><li>No added colours or preservatives</li><li>Serve on its own or over dry food</li></ul>',
                'additional_info' => '<p>Serve at room temperature. Once opened, cover and refrigerate, then use within two days. A 300g pack suits a medium dog as a single generous meal or two smaller ones.</p>',
                'images' => ['gobble-chicken-liver-rice-wet-dog-food.png'],
            ],
            [
                'category' => 'dog',
                'name' => 'Drools Vegetarian All Life Stages Dog Dry Food',
                'price' => 2150.00,
                'stock' => 30,
                'description' => '<p>A complete vegetarian kibble for households that keep a meat-free kitchen, balanced to cover every life stage.</p><ul><li>Fully vegetarian, nutritionally complete</li><li>Plant proteins with added amino acids</li><li>Suitable for puppies through to seniors</li><li>Added calcium for bones and teeth</li><li>Omega fatty acids for skin and coat</li></ul>',
                'additional_info' => '<p>Feed according to the chart on the pack, splitting the daily amount across two meals. Keep the bag sealed and use within six weeks of opening.</p>',
                'images' => ['drools-vegetarian-all-life-stages-dog-food.png'],
            ],

            // Cat
            [
                'category' => 'cat',
                'name' => 'Jomo Salmon and Sweet Potato Dry Cat Food 400g',
                'price' => 300.00,
                'stock' => 64,
                'description' => '<p>Real salmon paired with sweet potato in a everyday dry food for kittens over two months and adult cats alike.</p><ul><li>Salmon as the named first ingredient</li><li>Sweet potato for steady energy</li><li>Added fibre to support digestion</li><li>Omega fatty acids for a glossy coat</li><li>Enriched with vitamins A, D and E</li></ul>',
                'additional_info' => '<p>Refer to the feeding guide on the pack for daily amounts by weight and age. Cats on dry food should always have fresh water within reach.</p>',
                'images' => ['jomo-salmon-sweet-potato-dry-cat-food.jpg'],
            ],
            [
                'category' => 'cat',
                'name' => 'Me-O Creamy Treats Crab for Cats 60g',
                'price' => 100.00,
                'stock' => 150,
                'description' => '<p>A squeezable creamy treat that works as well for bonding and play as it does for hiding medication.</p><ul><li>Crab flavour cats respond to quickly</li><li>Taurine for healthy vision</li><li>Supports skin and coat condition</li><li>Feed from the tube or over food</li><li>Individually portioned sachets</li></ul>',
                'additional_info' => '<p>Offer one to two sachets a day as a treat rather than a meal. Store unopened sachets in a cool, dry place out of direct sunlight, and discard any left over once opened.</p>',
                'images' => ['me-o-creamy-treats-crab-for-cats.jpg'],
            ],
            [
                'category' => 'cat',
                'name' => 'Sheba Premium Fish with Sasami Wet Cat Food 35g Pack of 12',
                'price' => 600.00,
                'stock' => 18,
                'description' => '<p>Twelve single-serve pouches of bonito and sasami in gravy, portioned so nothing is left sitting in the bowl.</p><ul><li>Minimum 10% protein per pouch</li><li>Flaked fish in a light gravy</li><li>Around 20 kcal per pouch</li><li>Suits Persian, British Shorthair and Siamese</li><li>Twelve pouches per pack</li></ul>',
                'additional_info' => '<p>Serve alongside a complete dry food rather than as the only meal. Ingredients: fish (bonito), sasami, dried bonito flakes, polysaccharide thickener, seasoning. Keep fresh water available at all times.</p>',
                'images' => ['sheba-premium-fish-sasami-wet-cat-food.jpg'],
            ],
            [
                'category' => 'cat',
                'name' => 'Me-O Seafood Wet Cat Food 400g',
                'price' => 160.00,
                'stock' => 3,
                'description' => '<p>An everyday seafood wet food in a resealable tub, priced for cats that get a wet meal daily.</p><ul><li>Mixed seafood in jelly</li><li>High moisture supports urinary health</li><li>Complete and balanced for adult cats</li><li>Resealable 400g tub</li><li>No artificial colours</li></ul>',
                'additional_info' => '<p>An average adult cat needs roughly 200g a day, split across two meals, adjusted for weight and activity. Refrigerate after opening and use within 48 hours.</p>',
                'images' => ['me-o-sea-food-wet-cat-food-1.jpeg', 'me-o-sea-food-wet-cat-food-2.jpeg'],
            ],

            // Bird
            [
                'category' => 'bird',
                'name' => 'Boltz Cockatiel and Lovebird Food',
                'price' => 499.00,
                'stock' => 40,
                'description' => '<p>A seed mix blended for the smaller hookbills, with the millet and canary seed cockatiels and lovebirds pick out first.</p><ul><li>Balanced for cockatiels and lovebirds</li><li>Millet, canary seed and safflower</li><li>Cleaned and dust-extracted</li><li>Supports plumage condition</li><li>Resealable pack</li></ul>',
                'additional_info' => '<p>Offer one to two tablespoons per bird daily and refresh it every day. Blow off empty husks so full seed underneath is not missed. Provide cuttlebone and fresh water alongside.</p>',
                'images' => ['boltz-cockatiel-lovebird-food.png'],
            ],
            [
                'category' => 'bird',
                'name' => 'Boltz Striped Sunflower Seeds Bird Food',
                'price' => 336.00,
                'stock' => 55,
                'description' => '<p>Plain striped sunflower seed, useful as a treat or for mixing into a base ration for larger birds.</p><ul><li>Single-ingredient striped sunflower</li><li>High in fat and energy</li><li>Cleaned, graded and dust-free</li><li>Suits parrots, cockatiels and conures</li><li>Good for foraging toys</li></ul>',
                'additional_info' => '<p>Rich in oil, so use as part of a mix rather than a whole diet. Around ten percent of daily intake is a sensible ceiling for most species. Keep sealed in a cool, dry place.</p>',
                'images' => ['boltz-striped-sunflower-seeds-bird-food.png'],
            ],
            [
                'category' => 'bird',
                'name' => 'Boltz Big Parrot Mixed Seed Food',
                'price' => 552.00,
                'stock' => 26,
                'description' => '<p>A coarse mix for the larger parrots, with nuts and bigger seeds that give a strong beak something to work at.</p><ul><li>Formulated for macaws, greys and amazons</li><li>Nuts, maize, sunflower and pulses</li><li>Varied textures encourage foraging</li><li>Supports feather condition</li><li>Cleaned and dust-extracted</li></ul>',
                'additional_info' => '<p>Serve as the base ration with fresh fruit and vegetables daily. Replace uneaten seed each morning. Store in an airtight container away from heat.</p>',
                'images' => ['boltz-big-parrot-mixed-seed-food.png'],
            ],
            [
                'category' => 'bird',
                'name' => 'Versele Laga Prestige Food for Parrots',
                'price' => 720.00,
                'stock' => 15,
                'description' => '<p>A European-blended parrot mix with a reputation for consistency between bags and very little waste.</p><ul><li>Consistent, carefully graded blend</li><li>Cleaned to a high purity standard</li><li>High acceptance, so less is thrown out</li><li>Suits a broad range of parrot species</li><li>Supports digestion and plumage</li></ul>',
                'additional_info' => '<p>Feed daily as the main ration, topped up with fruit, vegetables and a mineral block. Adjust quantities for species and body condition. Reseal after each use.</p>',
                'images' => ['versele-laga-prestige-parrot-food.png'],
            ],
            [
                'category' => 'bird',
                'name' => 'ZuPreem FruitBlend Large Bird Food',
                'price' => 1275.00,
                'stock' => 12,
                'description' => '<p>An extruded pellet rather than a seed mix, so a selective bird cannot pick around the parts it likes least.</p><ul><li>Every pellet nutritionally complete</li><li>Natural fruit flavours and colours</li><li>Sized for larger birds</li><li>Removes selective feeding entirely</li><li>Less mess and waste than loose seed</li></ul>',
                'additional_info' => '<p>Move across from seed gradually over a week or two, since birds used to seed often need time to accept pellets. Keep fresh water available and refresh the bowl daily.</p>',
                'images' => ['zupreem-fruitblend-large-bird-food.png'],
            ],

            // Small Animals
            [
                'category' => 'small-animals',
                'name' => 'Excel Forage and Feast Hay Bar with Marigold',
                'price' => 459.00,
                'stock' => 70,
                'description' => '<p>A compressed timothy hay bar with marigold, giving rabbits and guinea pigs something to chew through rather than simply eat.</p><ul><li>Timothy hay with dried marigold</li><li>Chewing helps wear teeth evenly</li><li>High fibre supports gut movement</li><li>Encourages natural foraging</li><li>For rabbits, guinea pigs and chinchillas</li></ul>',
                'additional_info' => '<p>Offer as an addition to unlimited feeding hay, not as a replacement for it. One bar per week per animal is a reasonable guide. Store somewhere dry so the hay does not soften.</p>',
                'images' => ['excel-forage-feast-hay-bar-marigold.png'],
            ],
            [
                'category' => 'small-animals',
                'name' => 'Rabbit Food Pellets Highly Nutritious Diet 1kg',
                'price' => 389.00,
                'stock' => 45,
                'description' => '<p>A uniform pellet for rabbits, so every mouthful carries the same balance instead of letting favourites get picked out.</p><ul><li>Single pellet, no selective feeding</li><li>High fibre for digestive health</li><li>Added vitamin C and minerals</li><li>Supports healthy teeth</li><li>1kg resealable pack</li></ul>',
                'additional_info' => '<p>Pellets are a supplement to hay, not a substitute: hay should still make up the bulk of the diet. Around 25g per kilogram of body weight daily suits most adult rabbits.</p>',
                'images' => ['rabbit-food-pellets-1kg.png'],
            ],
            [
                'category' => 'small-animals',
                'name' => 'Hamster Food Pellets Premium Nutritious Diet 2kg',
                'price' => 680.00,
                'stock' => 5,
                'description' => '<p>A complete pellet for hamsters and gerbils, sized small enough to carry and store in a cheek pouch.</p><ul><li>Complete diet in one pellet</li><li>Protein level suited to small rodents</li><li>Small enough to pouch and hoard</li><li>Added vitamins and minerals</li><li>2kg resealable pack</li></ul>',
                'additional_info' => '<p>Offer one to two tablespoons daily. Hamsters hoard, so check bedding for caches and clear any damp or perishable food. Fresh water should always be available.</p>',
                'images' => ['hamster-food-pellets-2kg.png'],
            ],
            [
                'category' => 'small-animals',
                'name' => 'Excel Winter Berry Bakes Limited Edition',
                'price' => 389.00,
                'stock' => 25,
                'description' => '<p>A seasonal baked treat with winter berries, for rabbits and guinea pigs that have earned something out of the ordinary.</p><ul><li>Baked with real winter berries</li><li>Fibre-rich base</li><li>No added sugar</li><li>Limited seasonal production</li><li>For rabbits, guinea pigs and chinchillas</li></ul>',
                'additional_info' => '<p>A treat rather than a staple: one or two pieces a week is plenty. Introduce slowly for animals not used to fruit flavours. Store sealed in a cool, dry place.</p>',
                'images' => ['excel-winter-berry-bakes.png'],
            ],
            [
                'category' => 'small-animals',
                'name' => 'Excel Chinchilla Nuggets with Mint',
                'price' => 1298.00,
                'stock' => 20,
                'description' => '<p>A nugget formulated specifically for chinchillas, whose fibre needs and low fat tolerance are not met by generic small-animal mixes.</p><ul><li>Formulated for chinchillas specifically</li><li>High fibre, low fat</li><li>Mint for palatability</li><li>Added vitamin C</li><li>Uniform nugget prevents selective feeding</li></ul>',
                'additional_info' => '<p>Feed roughly two tablespoons daily alongside unlimited feeding hay. Chinchillas digest fat poorly, so avoid nuts and seeds as extras. Keep fresh water available.</p>',
                'images' => ['excel-chinchilla-nuggets-mint.png'],
            ],
        ];
    }
}
