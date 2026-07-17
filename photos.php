<?php
require_once 'config.php';
require_once 'includes/functions.php';

$page_title = 'معرض الصور';
$active = 'photos';
$base = '';
$asset_base = '';

// جلب الصور المرئية فقط
$photos = [];
$res = $conn->query("SELECT * FROM photos WHERE is_visible = 1 ORDER BY sort_order");
if ($res) {
    $photos = $res->fetch_all(MYSQLI_ASSOC);
}

require 'includes/header.php';
?>

<section class="page-title">
  <div class="container">
    <h1>📸 معرض صورنا</h1>
    <p>تصفح صور أطباقنا الشهية وأجواء مطعمنا الدافئة.</p>
  </div>
</section>

<div class="container">
  <section class="gallery-section">
    <div class="gallery-grid">
      <?php if (empty($photos)): ?>
        <div style="text-align: center; padding: 40px; grid-column: 1/-1;">
          <p style="color: var(--muted); font-size: 16px;">لا توجد صور حتى الآن. تابعنا قريبًا! 📷</p>
        </div>
      <?php else: ?>
        <?php foreach ($photos as $photo): ?>
          <div class="gallery-item" onclick="openGalleryModal('<?= h($photo['filename']) ?>')">
            <img src="uploads/<?= h($photo['filename']) ?>" alt="<?= h($photo['title_ar']) ?>" loading="lazy">
            <div class="gallery-overlay">
              <h3><?= h($photo['title_ar']) ?></h3>
              <p><?= h($photo['description_ar'] ?? '') ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>

<!-- Gallery Modal -->
<div id="galleryModal" class="modal" onclick="closeGalleryModal(event)">
  <div class="modal-content gallery-modal">
    <span class="close-btn" onclick="closeGalleryModal()">&times;</span>
    <img id="modalImage" src="" alt="Photo">
  </div>
</div>

<?php require 'includes/footer.php'; ?>

<style>
.gallery-section {
  padding: 40px 0;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
}

.gallery-item {
  position: relative;
  overflow: hidden;
  border-radius: 8px;
  aspect-ratio: 1;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.gallery-item:hover {
  transform: scale(1.05);
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.gallery-item:hover img {
  transform: scale(1.1);
}

.gallery-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  color: white;
  padding: 20px;
  transform: translateY(100%);
  transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
  transform: translateY(0);
}

.gallery-overlay h3 {
  margin: 0 0 5px 0;
  font-size: 16px;
}

.gallery-overlay p {
  margin: 0;
  font-size: 13px;
  opacity: 0.9;
}

.modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.9);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}

.modal.active {
  display: flex;
}

.gallery-modal {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
}

.gallery-modal img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.close-btn {
  position: absolute;
  top: 20px;
  right: 30px;
  color: white;
  font-size: 36px;
  cursor: pointer;
  z-index: 1001;
}

.close-btn:hover {
  color: #ccc;
}
</style>

<script>
function openGalleryModal(filename) {
  const modal = document.getElementById('galleryModal');
  const img = document.getElementById('modalImage');
  img.src = 'uploads/' + filename;
  modal.classList.add('active');
}

function closeGalleryModal(event) {
  if (event && event.target.id !== 'galleryModal') return;
  const modal = document.getElementById('galleryModal');
  modal.classList.remove('active');
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeGalleryModal();
});
</script>
