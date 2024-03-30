<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

class Git
{

    static public function getCurrentCommit(){
        $commit=array();
        $commit['build'] = trim(shell_exec("git rev-parse HEAD"));
        $commit['date'] = trim(shell_exec('git log -1 --format=%cd --date=format:"%Y-%m-%d %H:%M:%S"'));
        $commit['comment'] = trim(shell_exec('git log -1 --pretty=%B'));

        $version_mineur = trim(shell_exec('git rev-list $(git describe --tags `git rev-list --tags --max-count=1`)..HEAD --count'));
        $version_majeur = trim(shell_exec('git describe --tags `git rev-list --tags --max-count=1`'));

        $elems = explode(".",$version_majeur);

        if (count($elems) === 3) {
            $elems[2] = $version_mineur;
        }

        $version = implode(".", $elems);
        $commit['version'] = $version;

        return $commit;
    }

    static public function getNewCommit($build){
        
        
        $commits = shell_exec('git log '.$build.'..HEAD --pretty=format:"%H"');

        if (!empty($commits)){
            $commits = trim($commits);
        }
        else{
            $commits = "";
        }
        
        $commits_following = explode("\n", $commits);
        array_unshift($commits_following, $build);

        return $commits_following;
    }


}

/*



        $build = 
        $date = 
        $comment = 
*/