    </main><!-- /.db-main -->
  </div><!-- /.db-content-wrap -->
</div><!-- /.db-wrapper -->

<script>
(function () {
    var btn  = document.getElementById('dbToggleBtn');
    var side = document.getElementById('dbSidebar');
    var wrap = document.getElementById('dbContentWrap');
    if (btn) {
        btn.addEventListener('click', function () {
            side.classList.toggle('db-sidebar--collapsed');
            wrap.classList.toggle('db-content-wrap--expanded');
        });
    }
})();
</script>
</body>
</html>
