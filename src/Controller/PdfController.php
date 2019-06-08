<?php

/*
 * This file is part of the AdminLTE-Bundle demo.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * Pdf controller.
 */
class PdfController extends Controller
{
    /**
     * @Route("/print", defaults={}, name="print")
     */
    public function print($id)
    {
        // get user
        $repo = $this->getDoctrine()->getRepository('App:ListTable');

        $user = $this->getUser();

        $repoModel = $this->getDoctrine()->getRepository('App:Model');

        $listable = $repo->findByUser($user->getId(), $id);

        $model = $repoModel->find($id);

        $formation = [];
        if (!empty($listable)) {
            foreach ($listable as $row) {
                $formation[$row->getName()]['realise'] = $row->getRealise();
                $formation[$row->getName()]['value'] = $row->getValue();
            }
        }

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('default/print.html.twig', [
            'formation' => $formation,
            'user' => $user,
            'model' => $model,
        ]);

        $html = $this->renderView('pdf/pdf.html.twig', [
            'some' => $formation,
        ]);

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    /**
     * @Route("/print2", defaults={}, name="print2")
     */
    public function print2($id)
    {
        // get user
        $repo = $this->getDoctrine()->getRepository('App:ListTable');

        $user = $this->getUser();

        $repoModel = $this->getDoctrine()->getRepository('App:Model');

        $listable = $repo->findByUser($user->getId(), $id);

        $model = $repoModel->find($id);

        $formation = [];
        if (!empty($listable)) {
            foreach ($listable as $row) {
                $formation[$row->getName()]['realise'] = $row->getRealise();
                $formation[$row->getName()]['value'] = $row->getValue();
            }
        }
        $html = $this->renderView('pdf/pdf.html.twig', [
            'formation' => $formation,
            'user'=>$user,
            'model'=>$model
        ]);

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'file.pdf'
        );
    }
}
