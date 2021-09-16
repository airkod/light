$(document).ready(function () {

  $(document).on('click', '[data-element-imagesingle] [data-select]', function () {

    let selectButton = $(this);

    modal.container('<iframe class="storage-iframe" src="' + selectButton.closest('[data-element-imagesingle]').data('storage-url') + '"></iframe>', null, {wide: true}, 'Управление файлами');

    window.selectImage = function (image) {

      if (!selectButton) {
        return;
      }

      let elementContainer = selectButton.closest('[data-element-imagesingle]');

      elementContainer.find('input').val(image);

      const ext = image.split('.')[image.split('.').length - 1];

      let template = '';

      if (ext === 'pdf') {
        template = elementContainer.find('[data-template-pdf]').html().split('{{value}}').join(image);
      }
      else if (ext === 'jpg' || ext === 'jpeg' || ext === 'png') {
        template = elementContainer.find('[data-template-image]').html().split('{{value}}').join(image);
      }
      else {
        template = elementContainer.find('[data-template-file]').html().split('{{value}}').join(image);
      }

      elementContainer.find('[data-image-container]').html(template);

      modal.hide();
    };
  });

  $(document).on('click', '[data-element-imagemultiple] [data-remove]', function () {

    var $remove = $(this);

    modal.confirm('Удалить изображение?', () => {
      $remove.parent().remove();
    });

    return false;
  });

  $(document).on('click', '[data-element-imagemultiple] [data-select]', function () {

    let selectButton = $(this);

    modal.container('<iframe class="storage-iframe" src="' + selectButton.closest('[data-element-imagemultiple]').data('storage-url') + '"></iframe>', null, {wide: true}, 'Управление файлами');

    window.selectImage = function (image) {

      if (!selectButton) {
        return;
      }

      let elementContainer = selectButton.closest('[data-element-imagemultiple]');

      let template = elementContainer.find('[data-template]').html().split('{{value}}').join(image);

      elementContainer.find('[data-image-container]').append(template);

      modal.hide();
    };
  });

  setInterval(() => {
    let images = $('[data-element-imagemultiple] [data-image-container]');
    if (images.length) {
      if (!images.attr('data-initialized')) {
        images.attr('data-initialized', 'true');
        images.sortable({});
      }
    }
  }, 200);

});