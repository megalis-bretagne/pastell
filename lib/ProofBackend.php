<?php

interface ProofBackend
{
    public function write($id, $content): void;

    public function read($id);
}