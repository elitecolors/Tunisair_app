<?php

/*
 * This file is part of the AdminLTE-Bundle demo.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Form\FormDemoModelType;
use App\Repository\ListTableRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use SpreadsheetReader;
use ForceUTF8\Encoding;
use App\Entity\Model;
use App\Entity\ListTable;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Default controller.
 */
class AdminController extends Controller
{
    /**
     * @Route("/", defaults={}, name="homepage")
     */
    public function index()
    {
        return $this->render('default/index.html.twig', []);
    }

    /**
     * @Route("/html", defaults={}, name="html")
     */
    public function showHtml()
    {
        return $this->render('default/print_html.html.twig', []);
    }

    /**
     * @Route("/update_file", defaults={}, name="update_file")
     */
    public function updateFile(Request $request)
    {
        $form = $this->createForm(FormDemoModelType::class);
        $form = $this->handleForm($request, $form);

        return $this->render('default/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    protected function handleForm(Request $request, FormInterface $form)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $file = $request->files->get('form_demo_model')['file'];
                $this->saveFile($file);
                $this->updateDatabase();

                $this->addFlash('success', 'Fantastic work! You nailed it, form has no errors :-)');
            } else {
                $this->addFlash('error', 'Form has errors ... please fix them!');
            }
        }

        return $form;
    }

    /**
     * uplaod file
     * /@todo create service and check extension file only xls.
     *
     * @param $file
     *
     * @return bool
     */
    private function saveFile($file)
    {
        $original_name = $file->getClientOriginalName();
        $file->move($this->getParameter('xls_files_directory'), $original_name);

        return true;
    }

    private function updateDatabase()
    {
        /**
         * @var EntityManager
         */
        $entityManager = $this->getDoctrine()->getManager();

        $file = $this->getParameter('xls_files_directory').'/xls.xls';
        if (!file_exists($file)) {
            throw new \Exception('File does not exist');
        }
        $Reader = new SpreadsheetReader($file);
        $Sheets = $Reader->Sheets();

        // CLEAN TABLE MODEL AND LIST TABLE BEFORE INSERTION
        $this->cleanDatabase($entityManager);

        foreach ($Sheets as $Index => $Name) {
            if (false !== strpos($Name, 'Feuil')) {
                continue;
            }

            $model = new Model();
            $model->setName(Encoding::fixUTF8($Name));

            $entityManager->persist($model);
            $entityManager->flush();

            $Reader->ChangeSheet($Index);

            foreach ($Reader as $Row) {
                if (!empty($Row[1]) && !empty($Row[2]) && 'REALISE' != $Row[3]) {
                    $user = $this->createUser($Row, $entityManager);

                    $this->saveFormation($Row, $user, $model->getId(), $entityManager);
                }
            }
        }
    }

    /**
     * add user.
     *
     * @param $row
     * @param $db
     *
     * @return bool|mixed
     */
    private function createUser($row, $db)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $email = Encoding::fixUTF8($row[2].'@tunisair.com');
        $username = Encoding::fixUTF8($row[2]);
        $password = Encoding::fixUTF8($row[1]);
        $user_exist = $userManager->findUserByEmail($email);

        // Check if the user exists to prevent Integrity constraint violation error in the insertion
        if ($user_exist) {
            return $user_exist->getId();
        }

        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setEmailCanonical($email);
        $user->setEnabled(1); // enable the user or enable it later with a confirmation token in the email
        // this method will encrypt the password with the default settings :)
        $user->setPlainPassword($password);
        $user->setStufNumber($row[1]);
        $userManager->updateUser($user);

        return $user->getId();
    }

    /**
     * @param $row
     * @param $user
     * @param $model
     * @param $db
     *
     * @return bool
     */
    private function saveFormation($row, $user, $model, $db)
    {
        $listTable = new ListTable();
        $listTable->setName('M.D./2ans');
        $listTable->setRealise($row[4]);
        $listTable->setValue($row[6]);
        $listTable->setIdModel($model);
        $listTable->setIdUser($user);

        $db->persist($listTable);
        $db->flush();

        $listTable = new ListTable();
        $listTable->setName('S.S./1an');
        $listTable->setRealise($row[7]);
        $listTable->setValue($row[9]);
        $listTable->setIdModel($model);
        $listTable->setIdUser($user);

        $db->persist($listTable);
        $db->flush();

        $listTable = new ListTable();
        $listTable->setName('FH');
        $listTable->setRealise($row[10]);
        $listTable->setValue($row[12]);
        $listTable->setIdModel($model);
        $listTable->setIdUser($user);

        $db->persist($listTable);
        $db->flush();

        $listTable = new ListTable();
        $listTable->setName('SURETE');
        $listTable->setRealise($row[13]);
        $listTable->setValue($row[15]);
        $listTable->setIdModel($model);
        $listTable->setIdUser($user);

        $db->persist($listTable);
        $db->flush();

        $listTable = new ListTable();
        $listTable->setName('C1');
        $listTable->setRealise($row[16]);
        $listTable->setValue($row[18]);
        $listTable->setIdModel($model);
        $listTable->setIdUser($user);

        $db->persist($listTable);
        $db->flush();

        $listTable = new ListTable();
        $listTable->setName('C2');
        $listTable->setRealise($row[19]);
        $listTable->setValue($row[21]);
        $listTable->setIdModel($model);
        $listTable->setIdUser($user);

        $db->persist($listTable);
        $db->flush();

        $listTable = new ListTable();
        $listTable->setName('LICENCE');
        $listTable->setRealise($row[21]);
        $listTable->setValue($row[21]);
        $listTable->setIdModel($model);
        $listTable->setIdUser($user);

        $db->persist($listTable);
        $db->flush();

        return true;
    }

    /**
     * @param EntityManager $db
     * @throws \Doctrine\DBAL\DBALException
     */
    private function cleanDatabase(EntityManager $db)
    {
        $connection = $db->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('model', true /* whether to cascade */));
        $connection->executeUpdate($platform->getTruncateTableSQL('list_table', true /* whether to cascade */));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formation($id)
    {
        /**
         * @var ListTableRepository
         */
        $repos = $this->getDoctrine()
            ->getRepository(ListTable::class);

        $user = $this->getUser()->getId();

        $result = $repos->findByUser($user, $id);

        return $this->render('default/formation.html.twig', ['data' => $result, 'model' => $id]);
    }

    public function userPreferences()
    {
        return $this->render('control-sidebar/settings.html.twig', []);
    }
}
