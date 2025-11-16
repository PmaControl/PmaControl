<?php
// $data['software'] = [ ['id'=>..,'libelle'=>..], ... ]
// $data['majors'][software_id] = [ ['id'=>major,'libelle'=>major], ... ]
// $data['tags'][software_id][major] = [ ['id'=>id_image,'libelle'=>tag], ... ]
?>


<form method="post" action="<?=LINK?>docker/addContainer/<?=$id_docker_server ?>">

<table class="table table-bordered table-condensed" id="container-table">
<thead>
<tr>
    <th>Instances</th>
    <th>Family</th>
    <th>Major</th>
    <th>Version</th>
    <th>Label</th>
    <th style="width:50px;">Actions</th>
</tr>
</thead>

<tbody id="rows">
<tr class="container-row">
    <td>
        <select name="docker_container[count][]" class="form-control select-count"></select>
    </td>

    <td>
        <select name="docker_container[id_software][]" class="form-control select-software">
            <option value="">-- choose --</option>
            <?php foreach ($data['software'] as $s): ?>
                <option value="<?=$s['id']?>"><?=$s['libelle']?></option>
            <?php endforeach; ?>
        </select>
    </td>

    <td>
        <select name="docker_container[major][]" class="form-control select-major">
            <option value="">-- major --</option>
        </select>
    </td>

    <td>
        <select name="docker_container[id_image][]" class="form-control select-tag">
            <option value="">-- version --</option>
        </select>
    </td>

    <td>
        <input type="text" name="docker_container[label][]" class="form-control" placeholder="optional name">
    </td>

    <td class="text-center">
        <button type="button" class="btn btn-danger btn-delete" disabled>🗑</button>
    </td>
</tr>
</tbody>

</table>

<button type="button" class="btn btn-primary" id="btn-add-row"><span class="glyphicon glyphicon-plus"></span> Add a container</button>
<button type="submit" class="btn btn-success">✓ Create containers</button>
<button type="button" class="btn btn-warning" id="btn-clear-all">
    <span class="glyphicon glyphicon-trash"></span> Clear all
</button>

</form>


<script>
    const MAJORS = <?=json_encode($data['majors'], JSON_UNESCAPED_SLASHES)?>;
const TAGS   = <?=json_encode($data['tags'], JSON_UNESCAPED_SLASHES)?>;

const rowsTbody = document.getElementById('rows');
const addBtn = document.getElementById('btn-add-row');

const COUNTS = Array.from({length: 32}, (_, i) => ({id: i+1, libelle: (i+1).toString()}));

function populateCount(row, selected = 1) {
    const select = row.querySelector('.select-count');
    select.innerHTML = "";
    COUNTS.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c.id;
        opt.textContent = c.libelle;
        if (c.id == selected) opt.selected = true;
        select.appendChild(opt);
    });
}

function buildOptions(select, array, selectedId) {
    select.innerHTML = "";
    array.forEach(o => {
        const opt = document.createElement("option");
        opt.value = o.id;
        opt.textContent = o.libelle;
        if (o.id == selectedId) opt.selected = true;
        select.appendChild(opt);
    });
}

function populateTags(row, softwareId, majorKey, selectedTag = null) {
    const tagSel = row.querySelector('.select-tag');
    let tags = TAGS[softwareId]?.[majorKey] || [];

    // Insert ALL at top
    const tagsWithAll = [{id: "ALL", libelle: "ALL"}, ...tags];

    // Auto select latest version if none requested
    if (!selectedTag && tags.length > 0) {
        selectedTag = tags[tags.length - 1].id;
    }

    buildOptions(tagSel, tagsWithAll, selectedTag);
}

function populateMajors(row, softwareId, wantedMajor = null) {
    const majorSel = row.querySelector('.select-major');
    const majors = MAJORS[softwareId] || [];

    const majorsWithAll = [{id:"ALL", libelle:"ALL"}, ...majors];

    // auto-select latest real major if none requested
    if (!wantedMajor && majors.length > 0) {
        wantedMajor = majors[majors.length - 1].id;
    }

    buildOptions(majorSel, majorsWithAll, wantedMajor);

    if (wantedMajor !== "ALL") {
        populateTags(row, softwareId, wantedMajor);
    }
}

function populateSoftware(row, softwareId = null, majorKey = null, tagId = null) {
    const softSel = row.querySelector('.select-software');
    if (softwareId) softSel.value = softwareId;

    populateMajors(row, softwareId, majorKey);

    if (majorKey && majorKey !== "ALL") {
        populateTags(row, softwareId, majorKey, tagId);
    }
}

function currentSelections(row) {
    return {
        count: row.querySelector('.select-count').value,
        softwareId: row.querySelector('.select-software').value,
        major: row.querySelector('.select-major').value,
        tag: row.querySelector('.select-tag').value,
        label: row.querySelector('input[type="text"]').value
    };
}

function expandAllMajors(row, softwareId) {
    const majors = MAJORS[softwareId] || [];
    if (!majors.length) return;

    const template = row.cloneNode(true);
    row.remove();

    majors.forEach(m => {
        const newRow = template.cloneNode(true);
        populateCount(newRow, 1);
        newRow.querySelector('input[type="text"]').value = "";
        populateSoftware(newRow, softwareId, m.id, null);
        rowsTbody.appendChild(newRow);
    });

    enableDeletes();
}

function expandAllMinors(row, softwareId, majorKey) {
    const tags = TAGS[softwareId]?.[majorKey] || [];
    if (!tags.length) return;

    const template = row.cloneNode(true);
    row.remove();

    tags.forEach(t => {
        const newRow = template.cloneNode(true);

        populateCount(newRow, 1);
        newRow.querySelector('input[type="text"]').value = "";

        populateSoftware(newRow, softwareId, majorKey, t.id);

        rowsTbody.appendChild(newRow);
    });

    enableDeletes();
}


function enableDeletes() {
    const rows = rowsTbody.querySelectorAll('.container-row');
    rows.forEach(r => r.querySelector('.btn-delete').disabled = (rows.length === 1));
}

// Add new row
addBtn.addEventListener('click', () => {
    const lastRow = rowsTbody.querySelector('.container-row:last-child');
    const sel = currentSelections(lastRow);
    const newRow = lastRow.cloneNode(true);

    populateCount(newRow, sel.count);
    newRow.querySelector('input[type="text"]').value = sel.label;
    populateSoftware(newRow, sel.softwareId, sel.major, sel.tag);

    rowsTbody.appendChild(newRow);
    enableDeletes();
});

// Change handlers
rowsTbody.addEventListener('change', e => {
    const row = e.target.closest('.container-row');

    if (e.target.classList.contains('select-software')) {
        const sid = e.target.value;
        populateMajors(row, sid);
        populateCount(row, 1);
    }

    if (e.target.classList.contains('select-major')) {
        const sid = row.querySelector('.select-software').value;
        const maj = e.target.value;

        if (maj === "ALL") expandAllMajors(row, sid);
        else populateTags(row, sid, maj);
    }

    if (e.target.classList.contains('select-tag')) {
        const sid = row.querySelector('.select-software').value;
        const maj = row.querySelector('.select-major').value;
        const tag = e.target.value;

        if (tag === "ALL") expandAllMinors(row, sid, maj);
    }
});

// Delete
rowsTbody.addEventListener('click', e => {
    if (e.target.classList.contains('btn-delete')) {
        e.target.closest('.container-row').remove();
        enableDeletes();
    }
});

// Init
(function init() {
    const row = rowsTbody.querySelector('.container-row');
    populateCount(row, 1);
    enableDeletes();
})();


const clearBtn = document.getElementById('btn-clear-all');

clearBtn.addEventListener('click', () => {

    // On récupère la *première ligne modèle* avant d'effacer
    const firstRow = rowsTbody.querySelector('.container-row');
    if (!firstRow) return;

    const template = firstRow.cloneNode(true);

    // On vide toutes les lignes
    rowsTbody.innerHTML = "";

    // On recrée une ligne neuve à partir du template
    const newRow = template.cloneNode(true);

    // Reset valeurs
    populateCount(newRow, 1);
    newRow.querySelector('.select-software').value = "";
    newRow.querySelector('.select-major').innerHTML = "<option value=''>-- major --</option>";
    newRow.querySelector('.select-tag').innerHTML   = "<option value=''>-- version --</option>";
    newRow.querySelector('input[type="text"]').value = "";

    // Ajout dans le tableau
    rowsTbody.appendChild(newRow);

    // Disable delete (car 1 seule ligne)
    enableDeletes();
});

</script>
