
<a href="/pmacontrol/fr/dot3/download/" class="btn btn-primary" role="button"> Download <span class="glyphicon glyphicon-download-alt"></span></a>

<hr>

<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="exampleInputFile">Import JSON File</label>
        <input id="import-json" name="import[json]" type="file">
        <p class="help-block"><?= __("You import there JSON file previosly exported by the button download on top")?></p>
    </div>

    <button type="submit" class="btn btn-primary">Import JSON <svg style="vertical-align: middle;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-json"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 12a1 1 0 0 0-1 1v1a1 1 0 0 1-1 1 1 1 0 0 1 1 1v1a1 1 0 0 0 1 1"/><path d="M14 18a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1 1 1 0 0 1-1-1v-1a1 1 0 0 0-1-1"/></svg></button>
</form>