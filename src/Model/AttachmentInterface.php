<?php
namespace App\Model;

interface AttachmentInterface {

    public function setFile($file);

    public function getFile();

    public function getFileSecondary();

    public function setFileSecondary($file);

    public function getContentUrl(): ?string;

    public function setContentUrl(?string $contentUrl);

    public function getContentUrlSecondary(): ?string;

    public function setContentUrlSecondary(?string $contentUrlSecondary);
}