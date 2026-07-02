<?php
$galleryId = $galleryId ?? 'productGallery';
?>
<div class="product-gallery-container">
  <!-- Main Image Display -->
  <div class="product-gallery-main-wrapper rounded-lg overflow-hidden bg-light p-2" style="background-color: #f8f9fa;">
    <img id="mainProductImage" src="<?php echo htmlspecialchars($images[0]['public_path'] ?? $images[0]['image_path'] ?? 'images/products/accessories.jpg'); ?>" alt="Product" class="img-fluid w-100" style="object-fit: contain; min-height: 400px;">
  </div>

  <!-- Thumbnail Gallery -->
  <?php if (count($images) > 1): ?>
    <div class="gallery-thumbnails-wrapper mt-3">
      <div class="d-flex gap-2 overflow-auto pb-2 scrollbar-slim" style="scroll-behavior: smooth;">
        <?php foreach ($images as $index => $image):
          $btnClass = 'product-thumbnail-btn flex-shrink-0 mx-1';
          if ($index === 0) {
            $btnClass .= ' active';
          }
          $imageSrc = htmlspecialchars($image['public_path'] ?? $image['image_path'] ?? 'images/products/accessories.jpg');
        ?>
          <button type="button" class="<?php echo $btnClass; ?>" data-image-src="<?php echo $imageSrc; ?>" title="View image <?php echo $index + 1; ?>">
            <img src="<?php echo $imageSrc; ?>" alt="Thumbnail <?php echo $index + 1; ?>" class="img-fluid">
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<style>
  .product-gallery-container {
    width: 100%;
  }

  .product-gallery-main-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e9ecef;
    cursor: zoom-in;
    transition: all 0.3s ease;
  }

  .product-gallery-main-wrapper:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .gallery-thumbnails-wrapper {
    position: relative;
  }

  .gallery-thumbnails-wrapper .overflow-auto {
    scroll-snap-type: x mandatory;
  }

  .product-thumbnail-btn {
    width: 80px;
    height: 80px;
    padding: 4px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    scroll-snap-align: start;
  }

  .product-thumbnail-btn:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
  }

  .product-thumbnail-btn.active {
    border-color: #0d6efd;
    box-shadow: 0 3px 10px rgba(13, 110, 253, 0.25);
  }

  .product-thumbnail-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
  }

  @media (max-width: 768px) {
    .product-gallery-main-wrapper {
      min-height: 300px;
    }

    .product-thumbnail-btn {
      width: 70px;
      height: 70px;
    }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var mainImage = document.getElementById('mainProductImage');
  var thumbnails = document.querySelectorAll('.product-thumbnail-btn');
  
  if (!mainImage || thumbnails.length === 0) {
    return;
  }

  thumbnails.forEach(function(thumb) {
    thumb.addEventListener('click', function() {
      // Update main image
      mainImage.src = this.getAttribute('data-image-src');
      
      // Update active state
      thumbnails.forEach(function(t) {
        t.classList.remove('active');
      });
      this.classList.add('active');
      
      // Smooth scroll to thumbnail
      this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    });
  });
});
</script>
