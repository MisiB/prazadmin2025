<?php

namespace App\Interfaces\repositories;

interface iissuelogInterface
{
    public function getissuelogs();

    public function getissuelogsbyemail($email);

    public function getissuelog($id);

    public function createissuelog(array $data);

    public function updateissuelog($id, array $data);

    public function deleteissuelog($id);

    public function getissuegroups();

    public function getissuetypes();

    public function getissuetypesbygroup($issuegroupId);

    public function getuserissues($userId);

    public function getusercreatedissues($userId);

    public function getuserassignedissues($userId);

    public function updateissuestatus($id, $status);

    public function assignissue($id, $userId, $departmentId);

    public function getdepartmentissues($departmentId);

    public function getdepartmentusers($departmentId);

    public function getissueswithmetrics();

    public function getdepartmentturnaroundtime($departmentId);

    public function getuserturnaroundtime($userId);

    public function addcomment($issueId, $userEmail, $comment, $isInternal = false);

    public function getissuecomments($issueId);

    public function updatecomment($commentId, $userEmail, $comment, $isInternal = false);

    public function deletecomment($commentId);

    public function getcomment($commentId);
}
