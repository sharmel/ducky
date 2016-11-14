<?php if ($this->flashmessage): ?>
<?php 
    function getStatusIcon($status){
        switch ($status){
            case "error":
                return "times";
            case "warning":
                return "exclamation-triangle";
            case "info":
                return "info";
        }
        return "question-circle";
    }
?>
<div class="container-fluid">
    <div class="container">
        <br>
        <div class="col-md-12">
            <div class="alert alert-dismissable alert-<?= $this->flashmessage->status; ?>">
            <i class="fa fa-<?= getStatusIcon($this->flashmessage->status); ?>" aria-hidden="true"></i>
            &nbsp;
            <span>
                <?= $this->flashmessage->message; ?>
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
