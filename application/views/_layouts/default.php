<!DOCTYPE html>
<html lang="en">

<!-- header -->
<?= $this->renderPartial('headermeta'); ?>

<body id="page-top">
    <!-- Navigation -->
    <?= $this->renderPartial('navbar'); ?>

    <!-- messages -->
    <?= $this->renderPartial('flashmessage'); ?>

    <!-- Content -->
    <?= $this->getContent(); ?>

    <!-- footer -->
    <?= $this->renderPartial('footer'); ?>
</body>

</html>
