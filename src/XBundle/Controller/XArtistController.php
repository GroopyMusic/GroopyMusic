<?php

namespace XBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class XArtistController extends Controller
{

    /**
     * @Route("/dashboard", name="x_artist_dashboard")
     */
    public function dashboardAction()
    {
        return $this->render('XBundle:XArtist:dashboard_artist.html.twig');
    }


    /**
     * @Route("/project/new", name="x_artist_project_new")
     */
    public function newProjectAction()
    {
        return $this->render('XBundle:XArtist:project_new.html.twig');
    }


    /**
     * @Route("/project/details", name="x_artist_project_details")
     */
    public function detailsProjectAction()
    {
        return $this->render('XBundle:XArtist:project_details.html.twig');
    }


    /**
     * @Route("/project/update", name="x_artist_project_update")
     */
    public function updateProjectAction()
    {
        return $this->render('XBundle:XArtist:project_new.html.twig');
    }


    /**
     * @Route("project/donations-sales-details", name="x_artist_donations_sales_details")
     */
    public function donationsSalesDetailsAction()
    {
        // Real-time Chart
        $realTimeHistory = array(
            array(
                 "name" => "Total",
                 "data" => array(1, 2, 5, 5, 5, 5, 8)
            ),
            array(
                 "name" => "Ventes",
                 "data" => array(0, 2, 0, 1, 5, 3, 7)
            ),
            array(
                "name" => "Dons",
                "data" => array(1, 0, 5, 4, 0, 2, 1)
           ),

        );

        $days = array(
            "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"
        );

        $realtime = new Highchart();
        // ID de l'élement de DOM que vous utilisez comme conteneur
        $realtime->chart->renderTo('realtimechart');
        $realtime->title->text('Evolution des dons et des ventes en temps réel');
        $realtime->yAxis->title(array('text' => "Montant récolté (en €)"));
        $realtime->xAxis->title(array('text' => "Jour"));
        $realtime->xAxis->categories($days);
        $realtime->series($realTimeHistory);

        // Cumulative Chart
        $cumulHistory = array(
            array(
                 "name" => "Total",
                 "data" => array(136, 134, 154, 142, 147)
            ),
            array(
                 "name" => "Ventes",
                 "data" => array(86, 78, 90, 84, 87)
            ),
            array(
                "name" => "Dons",
                "data" => array(50, 56, 64, 58, 60)
           ),

        );

        $weeks = array(
            "1", "2", "3", "4", "5"
        );

        $cumul = new Highchart();
        // ID de l'élement de DOM que vous utilisez comme conteneur
        $cumul->chart->renderTo('cumulchart');
        $cumul->title->text('Evolution des dons et des ventes en cumulé');
        $cumul->yAxis->title(array('text' => "Montant récolté (en €)"));
        $cumul->xAxis->title(array('text' => "Semaine"));
        $cumul->xAxis->categories($weeks);
        $cumul->series($cumulHistory);

        return $this->render('XBundle:XArtist:donations_sales_details.html.twig', 
                                array('cumulchart' => $cumul,
                                      'realtimechart' => $realtime));
    }


    /**
     * @Route("project/products", name="x_artist_project_products")
     */
    public function productsDetailsAction()
    {
        return $this->render('XBundle:XArtist:project_products.html.twig');
    }

}

?>