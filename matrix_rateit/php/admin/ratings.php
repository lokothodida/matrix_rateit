<?php

  if (isset($_GET['delete'])) {
    $delete = $this->matrix->deleteRecord(self::TABLE_RATINGS, $_GET['delete']);
    if ($delete) {
      $undo = $url.'&undo='.$_GET['delete'];
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/DELETE_SUCCESS'), true, true, $undo);
    }
    else {
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/DELETE_ERROR'), false);
    }
  }
  elseif (isset($_GET['undo'])) {
    $undo = $this->matrix->undoRecord(self::TABLE_RATINGS, $_GET['undo']);
    if ($undo) {
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/UNDO_SUCCESS'), true);
    }
    else {
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/UNDO_ERROR'), false);
    }
  }

  $ratings = $this->matrix->query('SELECT * FROM '.self::TABLE_RATINGS.' ORDER BY id DESC');

?>

<!--header-->
<h3 class="floated"><?php echo i18n_r(self::FILE.'/RATINGS'); ?></h3>
<div class="edit-nav">
  <a href="<?php echo $url; ?>&config"><?php echo i18n_r(self::FILE.'/CONFIG'); ?></a>
  <a href="<?php echo $url; ?>" class="current"><?php echo i18n_r(self::FILE.'/RATINGS'); ?></a>
  <div class="clear"></div>
</div>

<style>
  <?php
    $css = file_get_contents(GSPLUGINPATH.self::FILE.'/css/rateit.css');
    $css = str_replace('../img/', $this->matrix->getSiteURL().'plugins/'.self::FILE.'/img/', $css);
    echo $css;
   ?>
</style>

<!--javascript-->
<script src="<?php echo $this->matrix->getSiteURL().'plugins/'.self::FILE.'/js/jquery.rateit.min.js'; ?>"></script>
<script>
  $(document).ready(function() {
    // pajinate the query
    $('.pajinate').pajinate({
      'nav_label_first' : '|&lt;&lt;', 
      'nav_label_prev'  : '&lt;', 
      'nav_label_next'  : '&gt;', 
      'nav_label_last'  : '&gt;&gt;|', 
    });
    $('.pajinate .page_navigation a').addClass('cancel');
    
    // filter
    $('#search_input').fastLiveFilter('.content');
    
    $('.delete').bind('click', function(e) {
      var id   = $(this).data('id');
      var slug = $(this).data('slug');
      e.preventDefault();
      $.Zebra_Dialog(<?php echo json_encode(i18n_r(TheMatrix::FILE.'/ARE_YOU_SURE')); ?>, {
        'type':     'question',
        'title':    <?php echo json_encode(i18n_r(TheMatrix::FILE.'/DELETE').' : '); ?> + slug,
        'buttons':  [
          {caption: <?php echo json_encode(i18n_r(TheMatrix::FILE.'/NO')); ?>, },
          {caption: <?php echo json_encode(i18n_r(TheMatrix::FILE.'/YES')); ?>, callback: function() { window.location = '<?php echo $url; ?>&ratings&delete=' + id }},
        ]
      });
    });
  }); // ready
</script>

<!--config-->

<div style="font-size: 90%; text-align: right;"><b><?php echo i18n_r(TheMatrix::FILE.'/FILTER'); ?></b> : <input id="search_input" type="text"></div>
<table class="pajinate edittable highlight">
  <thead>
    <tr>
      <th><?php echo i18n_r(self::FILE.'/SLUG'); ?></th>
      <th><?php echo i18n_r(self::FILE.'/VOTES'); ?></th>
      <th><?php echo i18n_r(self::FILE.'/TOTAL'); ?></th>
      <th><?php echo i18n_r(self::FILE.'/AVERAGE'); ?></th>
      <th></th>
      <th>%</th>
      <th></th>
    </tr>
  </thead>
  <tbody class="content">
    <?php
      foreach ($ratings as $rating) {
        $value = $rating['average'] * $this->config['stars'];
    ?>
      <tr>
        <td><?php echo $rating['slug']; ?></td>
        <td><?php echo $rating['votes']; ?></td>
        <td><?php echo $rating['total']; ?></td>
        <td><?php echo $rating['average']; ?></td>
        <td>
          <input type="range" value="<?php echo $value; ?>" step="0.5" id="backing<?php echo $rating['slug']; ?>">
          <div class="rateit" data-rateit-backingfld="#backing<?php echo $rating['slug']; ?>" data-rateit-resetable="false"  data-rateit-ispreset="true"
              data-rateit-value="<?php echo $value; ?>" data-rateit-ispreset="true" data-rateit-readonly="true" data-data-rateit-min="0" data-rateit-max="<?php echo $this->config['stars']; ?>"
              data-rateit-starwidth="<?php echo $this->config['width']; ?>" data-rateit-starheight="<?php echo $this->config['height']; ?>">
          </div>
        </td>
        <td><?php echo $rating['average'] * 100; ?>%</td>
        <td style="text-align: right;"><a href="#" data-id="<?php echo $rating['id']; ?>" data-slug="<?php echo $rating['slug']; ?>" class="delete cancel">&times;</a></td>
      </tr>
    <?php } ?>
    <?php if (empty($ratings)) { ?>
      <tr>
        <td colspan="100%"><?php echo i18n_r(self::FILE.'/NO_RATINGS'); ?></td>
      </tr>
    <?php } ?>
  </tbody>
  <thead>
    <tr>
      <th colspan="100%">
        <div class="page_navigation"></div>
      </th>
    </tr>
  </thead>
</table>