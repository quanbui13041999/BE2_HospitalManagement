<?php

namespace App\Exceptions;

use RuntimeException;

class ConcurrentModificationException extends RuntimeException
{
    public function __construct(
        string $message = 'Dữ liệu đã được người khác thay đổi. Trang sẽ được tải lại để cập nhật thông tin mới.'
    ) {
        parent::__construct($message, 409);
    }
}
