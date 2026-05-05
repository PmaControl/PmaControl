
<a class="btn btn-primary" href="<?=LINK?>docker/add">+ Add Docker Server</a>
<a class="btn btn-success" href="<?=LINK?>docker/list"><style>   .icon-docker-host {     width: 1em;     height: 1em;     vertical-align: -0.2em;     font-size: 1.2em; /* Zoom ×1.2 */   } </style>  <svg class="icon-docker-host" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">   <path d="M507,211.16c-1.42-1.19-14.25-10.94-41.79-10.94a132.55,132.55,0,0,0-21.61,1.9c-5.22-36.4-35.38-54-36.57-55l-7.36-4.28-4.75,6.9a101.65,101.65,0,0,0-13.06,30.45c-5,20.7-1.9,40.2,8.55,56.85-12.59,7.14-33,8.8-37.28,9H15.94A15.93,15.93,0,0,0,0,262.07,241.25,241.25,0,0,0,14.75,348.9C26.39,379.35,43.72,402,66,415.74,91.22,431.2,132.3,440,178.6,440a344.23,344.23,0,0,0,62.45-5.71,257.44,257.44,0,0,0,81.69-29.73,223.55,223.55,0,0,0,55.57-45.67c26.83-30.21,42.74-64,54.38-94h4.75c29.21,0,47.26-11.66,57.23-21.65a63.31,63.31,0,0,0,15.2-22.36l2.14-6.18Z"/>   <path d="M47.29,236.37H92.4a4,4,0,0,0,4-4h0V191.89a4,4,0,0,0-4-4H47.29a4,4,0,0,0-4,4h0v40.44a4.16,4.16,0,0,0,4,4h0"/>   <path d="M109.5,236.37h45.12a4,4,0,0,0,4-4h0V191.89a4,4,0,0,0-4-4H109.5a4,4,0,0,0-4,4v40.44a4.16,4.16,0,0,0,4,4"/>   <path d="M172.9,236.37H218a4,4,0,0,0,4-4h0V191.89a4,4,0,0,0-4-4H172.9a4,4,0,0,0-4,4h0v40.44a3.87,3.87,0,0,0,4,4h0"/>   <path d="M235.36,236.37h45.12a4,4,0,0,0,4-4V191.89a4,4,0,0,0-4-4H235.36a4,4,0,0,0-4,4h0v40.44a4,4,0,0,0,4,4h0"/>   <path d="M109.5,178.57h45.12a4.16,4.16,0,0,0,4-4V134.09a4,4,0,0,0-4-4H109.5a4,4,0,0,0-4,4v40.44a4.34,4.34,0,0,0,4,4"/>   <path d="M172.9,178.57H218a4.16,4.16,0,0,0,4-4V134.09a4,4,0,0,0-4-4H172.9a4,4,0,0,0-4,4h0v40.44a4,4,0,0,0,4,4"/>   <path d="M235.36,178.57h45.12a4.16,4.16,0,0,0,4-4V134.09a4.16,4.16,0,0,0-4-4H235.36a4,4,0,0,0-4,4h0v40.44a4.16,4.16,0,0,0,4,4"/>   <path d="M235.36,120.53h45.12a4,4,0,0,0,4-4V76a4.16,4.16,0,0,0-4-4H235.36a4,4,0,0,0-4,4h0v40.44a4.17,4.17,0,0,0,4,4"/>   <path d="M298.28,236.37H343.4a4,4,0,0,0,4-4V191.89a4,4,0,0,0-4-4H298.28a4,4,0,0,0-4,4h0v40.44a4.16,4.16,0,0,0,4,4"/> </svg> List of docker images</a>
<br><br>
<table class="table table-condensed table-bordered">
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Host</th>
    <th>SSH Key</th>
    <th>Status</th>
    <th></th>
</tr>

<?php foreach ($data['docker_servers'] as $server): ?>
<tr>
    <td><?=$server['id']?></td>
    <td><a href="<?= LINK ?>docker/server/<?= $server['id'] ?>"><?=$server['display_name']?></a></td>
    <td><?=$server['hostname']?>:<?=$server['port']?></td>
    <td><?=$server['ssh_key_name']?></td>
    <td><?=$server['is_active'] ? "✅" : "❌"?></td>
    <td>
        <a class="btn btn-xs btn-danger"
           href="<?=LINK?>docker/delete/<?=$server['id']?>"
           onclick="return confirm('Delete server <?=$server['display_name']?> ?');">
           Delete
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>