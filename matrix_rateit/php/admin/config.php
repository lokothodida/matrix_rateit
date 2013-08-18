<?php

  if (!empty($_POST)) {
    // update the record
    $update = $this->matrix->updateRecord(self::TABLE_CONFIG, 0, $_POST);
    
    if ($update) {
      $undo = $url.'&config&undo';
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/UPDATE_SUCCESS'), true, true, $undo);
    }
    else {
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/UPDATE_ERROR'), false);
    }
    
    
    
  }
  // undo changes
  elseif (isset($_GET['undo'])) {
    // undo the record update
    $undo = $this->matrix->undoRecord(self::TABLE_CONFIG, 0);
    
    if ($undo) {
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/UNDO_SUCCESS'), true);
    }
    else {
      $this->matrix->getAdminError(i18n_r(TheMatrix::FILE.'/UNDO_SUCCESS'), false);
    }
  }

?>

<!--header-->
<h3 class="floated"><?php echo i18n_r(self::FILE.'/CONFIG'); ?></h3>
<div class="edit-nav">
  <a href="<?php echo $url; ?>&config" class="current"><?php echo i18n_r(self::FILE.'/CONFIG'); ?></a>
  <a href="<?php echo $url; ?>"><?php echo i18n_r(self::FILE.'/RATINGS'); ?></a>
  <div class="clear"></div>
</div>

<!--config-->
<form method="post">
  <?php $this->matrix->displayForm(self::TABLE_CONFIG, 0); ?>
  <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>">
</form>