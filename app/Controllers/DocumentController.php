<?php
namespace App\Controllers;

use App\Repositories\DocumentoRepository;
use App\Services\NumeracionService;
use App\Models\Document;
use App\Models\Type;
use App\Models\Process;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocumentController {
  public function __construct(
    private DocumentoRepository $repo,
    private NumeracionService $nums,
    private \Twig\Environment $twig
  ){}

  public function index(Request $req, Response $res): Response {
    $q = $req->getQueryParams();
    $docs = $this->repo->paginateWithFilters($q, 10);
    $html = $this->twig->render('documents/index.twig', [
      'docs'=>$docs, 'filters'=>$q
    ]);
    $res->getBody()->write($html); return $res;
  }

  public function create(Request $req, Response $res): Response {
    $html = $this->twig->render('documents/create.twig', [
      'tipos'=>Type::all(), 'procesos'=>Process::all()
    ]);
    $res->getBody()->write($html); return $res;
  }

  public function store(Request $req, Response $res): Response {
    $d = (array)$req->getParsedBody();
    // Validaciones básicas (seguridad + UX)
    if (empty($d['DOC_NOMBRE']) || empty($d['DOC_ID_TIPO']) || empty($d['DOC_ID_PROCESO'])) {
      return $res->withStatus(422);
    }
    $tipoId = (int)$d['DOC_ID_TIPO']; $procId = (int)$d['DOC_ID_PROCESO'];
    $next = $this->nums->siguienteConsecutivo($tipoId, $procId);

    $this->repo->create([
      'DOC_NOMBRE'     => trim($d['DOC_NOMBRE']),
      'DOC_CONTENIDO'  => $d['DOC_CONTENIDO'] ?? null,
      'DOC_ID_TIPO'    => $tipoId,
      'DOC_ID_PROCESO' => $procId,
      'DOC_CODIGO'     => $next
    ]);

    return $res->withHeader('Location','/docs')->withStatus(302);
  }

  public function edit(Request $req, Response $res, array $args): Response {
    $doc = Document::findOrFail((int)$args['id']);
    $html = $this->twig->render('documents/edit.twig', [
      'doc'=>$doc, 'tipos'=>Type::all(), 'procesos'=>Process::all()
    ]);
    $res->getBody()->write($html); return $res;
  }

  public function update(Request $req, Response $res, array $args): Response {
    $doc = Document::findOrFail((int)$args['id']);
    $d = (array)$req->getParsedBody();
    $tipoId = (int)$d['DOC_ID_TIPO']; $procId = (int)$d['DOC_ID_PROCESO'];

    $nuevoCodigo = $this->nums->recalcularSiCambio($doc, $tipoId, $procId);

    $this->repo->update($doc, [
      'DOC_NOMBRE'     => trim($d['DOC_NOMBRE']),
      'DOC_CONTENIDO'  => $d['DOC_CONTENIDO'] ?? null,
      'DOC_ID_TIPO'    => $tipoId,
      'DOC_ID_PROCESO' => $procId,
      'DOC_CODIGO'     => $nuevoCodigo
    ]);
    return $res->withHeader('Location','/docs')->withStatus(302);
  }
}
