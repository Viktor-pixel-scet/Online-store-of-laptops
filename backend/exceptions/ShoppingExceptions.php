<?php
namespace Backend\Exceptions;

class ShoppingException extends \Exception
{
    protected $errorCode;
    
    public function __construct($message, $code = 0, \Throwable $previous = null, $errorCode = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }
    
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}

class ProductNotFoundException extends ShoppingException
{
    public function __construct($productId, $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Товар з ID $productId не знайдено", $code, $previous, 'product_not_found');
    }
}

class InsufficientStockException extends ShoppingException
{
    protected $availableStock;
    protected $requestedQuantity;
    
    public function __construct($productId, $availableStock, $requestedQuantity, $code = 0, \Throwable $previous = null)
    {
        $message = "На жаль, на складі доступно лише $availableStock шт.";
        parent::__construct($message, $code, $previous, 'insufficient_stock');
        $this->availableStock = $availableStock;
        $this->requestedQuantity = $requestedQuantity;
    }
    
    public function getAvailableStock()
    {
        return $this->availableStock;
    }
    
    public function getRequestedQuantity()
    {
        return $this->requestedQuantity;
    }
}

class ProductOutOfStockException extends ShoppingException
{
    public function __construct($productId, $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Товар тимчасово відсутній на складі.", $code, $previous, 'product_out_of_stock');
    }
}

class ValidationException extends ShoppingException
{
    protected $field;
    
    public function __construct($field, $message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous, 'validation_error');
        $this->field = $field;
    }
    
    public function getField()
    {
        return $this->field;
    }
}

class DatabaseException extends ShoppingException
{
    public function __construct($message = "Виникла помилка при обробці вашого запиту.", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous, 'database_error');
    }
}