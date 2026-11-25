<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\ProductRecord;
use Illuminate\Database\Seeder;

/**
 * Seeds the product catalogue: v1's catalogue (`ProdutoSeeder`, 212 products in Portuguese) in
 * English, 202 products. The ten v1 listed twice are merged: a singular and a plural ("Absorvente",
 * "Absorventes"), two words for one thing ("Cachaça", "Aguardente"), a brand next to its kind
 * ("Coca-Cola" and "Refrigerante", "Gatorade" and "Isotônico", "Sucrilhos" and "Cereais").
 */
final class ProductCatalogSeeder extends Seeder
{
    /**
     * Every product, grouped by department. Store 1 sells each department in the sector of the same
     * name (StoreOneSeeder); "General merchandise" has no sector there, so its products show up as
     * unsold on a visit.
     */
    public const array DEPARTMENTS = [
        'Bakery and breakfast' => [
            'Bread loaf', 'Cake', 'Cake mix', 'Cereal', 'Chewing gum', 'Chocolate', 'Chocolate powder',
            'Coffee', 'Cookies', 'Corn muffins', 'Crackers', 'Garlic bread', 'Hamburger buns',
            'Hot dog buns', 'Infant cereal', 'Jam', 'Mini bread rolls', 'Powdered milk', 'Savoury pie',
            'Snack cakes', 'Tea',
        ],
        'Household and personal care' => [
            'Bin bags', 'Bleach', 'Body lotion', 'Broom', 'Comb', 'Condoms', 'Conditioner', 'Cotton swabs',
            'Cotton wool', 'Dental floss', 'Deodorant', 'Dish soap', 'Dish towel', 'Disinfectant',
            'Disposable nappies', 'Disposable plates', 'Dog food', 'Dustpan', 'Fabric softener',
            'Floor cloth', 'Hairbrush', 'Insect repellent', 'Insecticide', 'Laundry powder',
            'Laundry soap bar', 'Leave-in conditioner', 'Mouthwash', 'Napkins', 'Paper towels',
            'Razor blades', 'Rubber gloves', 'Sanitary pads', 'Scrubbing brushes', 'Shampoo',
            'Shaving cream', 'Soap', 'Squeegee', 'Stain remover', 'Steel wool', 'Toilet paper',
            'Toothbrush', 'Toothpaste', 'Wet wipes',
        ],
        'Grocery' => [
            'Barbecue supplies', 'Beans', 'Black pepper', 'Brown rice', 'Canned goods', 'Cassava flour',
            'Condensed milk', 'Cooking oil', 'Cream', 'Crisps', 'Farofa', 'Gelatine', 'Hearts of palm',
            'Herbs', 'Mayonnaise', 'Olive oil', 'Oregano', 'Pasta', 'Peas', 'Pickles', 'Popcorn', 'Rice',
            'Sardines', 'Seasoning', 'Sugar', 'Sweetcorn', 'Sweetener', 'Tomato paste', 'Tomato sauce',
            'Tuna', 'Vinegar', 'Wheat flour', 'Wholewheat pasta', 'Worcestershire sauce', 'Yeast',
        ],
        'Drinks' => [
            'Beer', 'Cachaça', 'Chocolate milk', 'Coconut water', 'Energy drink', 'Ice', 'Iced tea', 'Juice',
            'Smoothie', 'Soft drinks', 'Sparkling wine', 'Spirits', 'Sports drink', 'Vodka', 'Water',
            'Whisky', 'Wine',
        ],
        'Fresh and frozen food' => [
            'Apples', 'Arracacha', 'Artichokes', 'Aubergine', 'Avocado', 'Bananas', 'Beetroot',
            'Bell peppers', 'Bratwurst', 'Broccoli', 'Butter', 'Cabbage', 'Carrots', 'Cassava',
            'Cauliflower', 'Cheese', 'Cheese bread', 'Chicken', 'Coriander', 'Courgette', 'Cream cheese',
            'Cucumber', 'Eggs', 'Escarole', 'Fermented milk', 'Fish', 'Frozen chips', 'Frozen fruit pulp',
            'Frozen vegetables', 'Fruit', 'Garlic', 'Grapes', 'Green beans', 'Guava', 'Ham', 'Hamburgers',
            'Hot dog sausages', 'Ice cream', 'Kale', 'Kiwi', 'Lasagne', 'Leafy greens', 'Lemons', 'Lettuce',
            'Liver', 'Mango', 'Margarine', 'Meat', 'Melon', 'Milk', 'Minced beef', 'Mortadella',
            'Mozzarella', 'Mushrooms', 'Nuggets', 'Onions', 'Oranges', 'Papaya', 'Parsley',
            'Passion fruit', 'Peaches', 'Pears', 'Pineapple', 'Pizza', 'Pork sausage', 'Potatoes',
            'Pressed ham', 'Pumpkin', 'Ready meals', 'Rocket', 'Skimmed milk', 'Smoked chicken breast',
            'Spring onions', 'Strawberries', 'Sweet potatoes', 'Swiss chard', 'Tangerines', 'Tomatoes',
            'Turkey breast', 'Vegetables', 'Watercress', 'Watermelon', 'Yoghurt',
        ],
        'General merchandise' => ['Batteries', 'Car care products', 'Flip-flops'],
    ];

    /**
     * Inserts every product, department after department.
     */
    public function run(): void
    {
        foreach (self::DEPARTMENTS as $products) {
            foreach ($products as $name) {
                ProductRecord::query()->create(['name' => $name]);
            }
        }
    }
}
