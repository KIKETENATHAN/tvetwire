/*hidden contents for cards*/
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const cardTitles = document.querySelectorAll('.card-title');
    cardTitles.forEach(title => {
      title.addEventListener('click', function() {
        const cardContent = this.nextElementSibling;
        cardContent.classList.toggle('hidden');
      });
    });
  });
</script>
/*hidden contents for cards end*/
