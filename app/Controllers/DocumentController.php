<?php
namespace App\Controllers;

use App\Repositories\DocumentRepository;
use App\Services\NumberingService;
use App\Models\Document;
use App\Models\Type;
use App\Models\Process;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocumentController {
  public function __construct(
    private DocumentRepository $repository,
    private NumberingService $numbering,
    private \Twig\Environment $twig
  ){}

  public function index(Request $req, Response $res): Response {
    $filters = $req->getQueryParams();
    $documents = $this->repository->paginateWithFilters($filters, 10);
    
    $html = $this->twig->render('documents/index.twig', [
      'docs' => $documents,
      'filters' => $filters
    ]);
    
    $res->getBody()->write($html);
    return $res;
  }

  public function create(Request $req, Response $res): Response {
    $html = $this->twig->render('documents/create.twig', [
      'tipos' => Type::all(),
      'procesos' => Process::all()
    ]);
    
    $res->getBody()->write($html);
    return $res;
  }

  public function store(Request $req, Response $res): Response {
    $data = (array)$req->getParsedBody();
    
    if (empty($data['DOC_NOMBRE']) || empty($data['DOC_ID_TIPO']) || empty($data['DOC_ID_PROCESO'])) {
      return $res->withStatus(422);
    }
    
    $typeId = (int)$data['DOC_ID_TIPO'];
    $processId = (int)$data['DOC_ID_PROCESO'];
    $nextCode = $this->numbering->getNextCode($typeId, $processId);

    $this->repository->create([
      'DOC_NOMBRE' => trim($data['DOC_NOMBRE']),
      'DOC_CONTENIDO' => $data['DOC_CONTENIDO'] ?? null,
      'DOC_ID_TIPO' => $typeId,
      'DOC_ID_PROCESO' => $processId,
      'DOC_CODIGO' => $nextCode
    ]);

    return $res->withHeader('Location', '/docs')->withStatus(302);
  }

  public function edit(Request $req, Response $res, array $args): Response {
    $document = Document::findOrFail((int)$args['id']);
    
    $html = $this->twig->render('documents/edit.twig', [
      'doc' => $document,
      'tipos' => Type::all(),
      'procesos' => Process::all()
    ]);
    
    $res->getBody()->write($html);
    return $res;
  }

  public function update(Request $req, Response $res, array $args): Response {
    $document = Document::findOrFail((int)$args['id']);
    $data = (array)$req->getParsedBody();
    
    $typeId = (int)$data['DOC_ID_TIPO'];
    $processId = (int)$data['DOC_ID_PROCESO'];

    $newCode = $this->numbering->recalculateIfChanged($document, $typeId, $processId);

    if (!$this->numbering->isCodeUnique($newCode, $typeId, $processId, $document->DOC_ID)) {
      return $res->withStatus(409);
    }

    $this->repository->update($document, [
      'DOC_NOMBRE' => trim($data['DOC_NOMBRE']),
      'DOC_CONTENIDO' => $data['DOC_CONTENIDO'] ?? null,
      'DOC_ID_TIPO' => $typeId,
      'DOC_ID_PROCESO' => $processId,
      'DOC_CODIGO' => $newCode
    ]);
    
    return $res->withHeader('Location', '/docs')->withStatus(302);
  }

  public function delete(Request $req, Response $res, array $args): Response {
    $document = Document::findOrFail((int)$args['id']);
    $this->repository->delete($document);
    
    return $res->withHeader('Location', '/docs')->withStatus(302);
  }
}
