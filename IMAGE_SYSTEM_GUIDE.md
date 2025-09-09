# GemCart Image System Guide

## How Category Images Work

The homepage now dynamically displays random images from each category for both the featured categories section and the category carousel.

### 1. Featured Categories Section

The three category cards that rotate daily now display:
- Random product images from the respective category
- If no product images are available, a random image from the category folder
- If no images are found, a placeholder with the category name

### 2. Category Carousel

The carousel section below the featured categories also uses:
- Random product images from each category
- Fallback to category folder images
- Placeholder as last resort

## How It Works

### Image Selection Priority

1. **Product Images**: The system first looks for images stored in the `products` table for the specific category
2. **Category Folder Images**: If no product images are found, it selects a random image from the category folder in `assets/`
3. **Placeholders**: If neither of the above are available, it uses a placeholder image with the category name

### Category Folder Structure

Images are organized in the `assets/` folder by category:
- `assets/rings/` - for ring products
- `assets/necklaces/` - for necklace products
- `assets/earrings/` - for earring products
- `assets/bracelets/` - for bracelet products
- `assets/watches/` - for watch products

### Image Selection Function

The `getRandomCategoryImage()` function in [index.php](file:///C:/xampp/htdocs/gemcart/index.php) handles image selection:

```php
function getRandomCategoryImage($category_id, $category_name, $connection) {
    // 1. Try to get a random product image from this category
    // 2. If no product images, try to find any image in the category folder
    // 3. Fallback to placeholder if no images found
}
```

## Adding New Images

### For Product Images
1. Add your image to the appropriate category folder in `assets/`
2. Update the product in the database with the relative path:
   ```sql
   UPDATE products SET image = 'rings/new-ring-design.jpg' WHERE id = 5;
   ```

### For Category Folder Images
1. Simply add images to the appropriate folder in `assets/`
2. The system will automatically pick them up

## Troubleshooting

### Images Not Showing
1. Check that the image file exists in the specified path
2. Verify the path is relative to the `assets/` folder
3. Ensure the file has proper read permissions
4. Check that the image format is supported (JPG, JPEG, PNG, GIF)

### Wrong Images Displaying
1. Check the product database entries for correct image paths
2. Verify there are no duplicate or incorrectly named files in category folders

## Customization

### Changing Image Selection Logic
Modify the `getRandomCategoryImage()` function in [index.php](file:///C:/xampp/htdocs/gemcart/index.php) to change how images are selected.

### Adding New Categories
1. Add the category to the database
2. Create a new folder in `assets/` with the category name
3. Add images to the folder
4. The system will automatically include the category

## Performance Notes

- The system uses `ORDER BY RAND()` for selecting random product images, which can be slow on large databases
- For better performance with large datasets, consider pre-selecting random images and caching them
- Image file sizes should be optimized for web use (recommended: 350x260px for category images)