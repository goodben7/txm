<?php
namespace App\Model;

interface AttachmentInterface {

    public function setFile($file);

    public function getFile();

    public function getContentUrl(): ?string;

    public function setContentUrl(?string $contentUrl);

    public function setContentUrlSecondary(?string $contentUrlSecondary);
}