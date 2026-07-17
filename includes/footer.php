  <footer class="site-footer" id="contact">
    <div class="container">
      <p>© <?= date('Y') ?> مطعم السلام — جميع الحقوق محفوظة</p>
      <p>📍 البيض، الجزائر &nbsp;•&nbsp; 📞 اتصل للطلب أو استعمل الموقع مباشرة</p>
      <p><a href="https://maps.app.goo.gl/RH6YCzz16s8m2ZrF7" target="_blank" rel="noopener noreferrer">افتح الخريطة في Google Maps</a></p>
    </div>
  </footer>
  <script src="<?= $asset_base ?? '' ?>assets/js/script.js?v=<?= file_exists(__DIR__ . '/../assets/js/script.js') ? filemtime(__DIR__ . '/../assets/js/script.js') : time() ?>"></script>
</body>
</html>
