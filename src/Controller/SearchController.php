<?php
namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search", methods={"GET"})
     */
    public function search(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
        }

        $products = $produitRepository->searchProducts($query);

        return new JsonResponse($products);
    }
}
