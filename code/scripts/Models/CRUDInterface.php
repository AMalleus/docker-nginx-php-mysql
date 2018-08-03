<?php

namespace Models;

interface CRUDInterface
{
    public function get();

    public function save();

    public function delete();
}