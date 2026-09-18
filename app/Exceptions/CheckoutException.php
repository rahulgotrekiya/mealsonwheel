<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Something stopped an order being placed that the customer can act on:
 * an empty basket, or stock that ran out between browsing and paying.
 */
class CheckoutException extends RuntimeException {}
