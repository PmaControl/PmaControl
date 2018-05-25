<form action="" method="post">

    <div class="panel panel-primary">
        <div class="panel-heading">

            <h3 class="panel-title"><?= __('Format SQL') ?></h3>
        </div>


        <div class="well">
            <div class="row">




                <div class="col-md-12">
                    Type your SQL here:<br />
                </div>
                <div class="col-md-12">
                    <textarea name="sql" rows="5" class="form-control">
<?php
if (!empty($data['sql'])) {
    echo $data['sql'];
}
?>
</textarea>
                </div>
                <div class="col-md-12">
                    <br />
                    <input class="btn btn-primary" type="submit" value="Submit">

                </div>
            </div>
        </div>
    </div>
</form>




<?php
if (!empty($data['sql_formated'])) {
    echo $data['sql_formated'];
}
?>