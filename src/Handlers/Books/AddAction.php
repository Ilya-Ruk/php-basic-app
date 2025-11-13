<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Handlers\Books;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rukavishnikov\Php\Basic\App\Models\Books\BookFactory;
use Rukavishnikov\Php\Basic\App\Repositories\Books\BookRepositoryInterface;
use Rukavishnikov\Php\Helper\Classes\JsonHelper;

final class AddAction implements RequestHandlerInterface
{
    /**
     * @param BookRepositoryInterface $bookRepository
     * @param JsonHelper $jsonHelper
     * @param ResponseInterface $response
     */
    public function __construct(
        private BookRepositoryInterface $bookRepository,
        private JsonHelper $jsonHelper,
        private ResponseInterface $response,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $book = BookFactory::createFromArray($request->getParsedBody());

        $id = $this->bookRepository->add($book);

        $data[$id] = "Book inserted!";

        $body = $this->jsonHelper->encode($data);
        $this->response->getBody()->write($body);

        return $this->response;
    }
}
