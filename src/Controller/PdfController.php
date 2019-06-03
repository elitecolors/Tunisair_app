<?php

/*
 * This file is part of the AdminLTE-Bundle demo.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dompdf\Options;

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
        $repo=$this->getDoctrine()->getRepository('App:ListTable');

        $user = $this->getUser();

        $repoModel=$this->getDoctrine()->getRepository('App:Model');

        $listable=$repo->findByUser($user->getId(),$id);

        $model=$repoModel->find($id);

        $formation=array();
        if(!empty($listable)){
            foreach ($listable as $row){
                $formation[$row->getName()]['realise']=$row->getRealise();
                $formation[$row->getName()]['value']=$row->getRealise();

            }
        }

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('default/print.html.twig', [
            'formation' => $formation,
            'user'=>$user,
            'model'=>$model
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();



        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }


}
