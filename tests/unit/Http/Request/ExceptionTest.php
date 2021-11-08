<?php

declare(strict_types=1);

namespace Baka\Test\Unit\Http\Request;

use Baka\Http\Exception\BadRequestException;
use Baka\Http\Exception\ForbiddenException;
use Baka\Http\Exception\InternalServerErrorException;
use Baka\Http\Exception\NotFoundException;
use Baka\Http\Exception\SessionNotFound;
use Baka\Http\Exception\UnauthorizedException;
use PhalconUnitTestCase;

class PhalconExceptionTestTest extends PhalconUnitTestCase
{
    public function testSessionDoesNotExist()
    {
        try {
            throw new SessionNotFound('Session not found');
        } catch (SessionNotFound $e) {
            $this->assertTrue($e->getHttpCode() === 499);
        }

        try {
            throw new BadRequestException('Session not found');
        } catch (BadRequestException $e) {
            $this->assertTrue($e->getHttpCode() === 400);
        }

        try {
            throw new ForbiddenException('Session not found');
        } catch (ForbiddenException $e) {
            $this->assertTrue($e->getHttpCode() === 403);
        }

        try {
            throw new InternalServerErrorException('Session not found');
        } catch (InternalServerErrorException $e) {
            $this->assertTrue($e->getHttpCode() === 500);
        }

        try {
            throw new NotFoundException('Session not found');
        } catch (NotFoundException $e) {
            $this->assertTrue($e->getHttpCode() === 404);
        }

        try {
            throw new UnauthorizedException('Session not found');
        } catch (UnauthorizedException $e) {
            $this->assertTrue($e->getHttpCode() === 401);
        }
    }
}
