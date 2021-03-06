<?php

//unimplemented: --uncompressed

include_once '../../VersionControl/Hg.php';

$old_repository = 'H:\Development\_Webroot\Trunk\Tests\Fixtures\Test_Repository' ;
$new_repository = 'H:\Development\_Webroot\Trunk\Tests\Fixtures\Clone_of_Repository';

$hg = new VersionControl_Hg();

/* Rely on Init.php to do work? */

/*r_rmdir($new_repository);
$cloned_repository_object_1 =
    $hg->clone($old_repository)->to($new_repository)->run();

var_dump($cloned_repository_object_1);
*/

//r_rmdir($new_repository);
$cloned_repository_object_2 =
    $hg->clone($old_repository)->revision('e5e678260dfe')->to($new_repository)->run();

var_dump($cloned_repository_object_2);
//@TODO check to ensure its at rev 4
die;





r_rmdir($new_repository);
$cloned_repository_object_3 =
    $hg->clone($old_repository)->revision('e5e678260dfe')->to($new_repository)->run();

var_dump($cloned_repository_object_3);


function r_rmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") r_rmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }

die('end of test');



//default is to create a working copy as well; only('repository') is the same
//as hg update null and kills the working copy.
// -U option
$cloned_repository_object_4 =
    //What? do we have a only('working-copy') as well?
    $hg->clone($old_repository)->to($new_repository)->only('repository')->run();
    //makes little sense for a function to have a single argument...
    $hg->clone($old_repository)->to($new_repository)->without('working-copy')->run();
    //no_working_copy() is too verbose, and we don't want/like underscored function names
    $hg->clone($old_repository)->to($new_repository)->no_working_copy()->run();
    //no_update does not clearly state its function
    $hg->clone($old_repository)->to($new_repository)->no_update()->run();
    //probably better; 'sparse' is already a term is some VCs implementations
    $hg->clone($old_repository)->to($new_repository)->sparse()->run();
    //even more semantic, but sounds kind of ominous
    $hg->clone($old_repository)->to($new_repository)->emptied()->run();


// -b options
$cloned_repository_object_5 =
    $hg->clone($old_repository)->branch('new_feature')->to($new_repository)->run();

    //keep() = keeps the working copy.

echo $cloned_repository_object;
var_dump($cloned_repository_object);
