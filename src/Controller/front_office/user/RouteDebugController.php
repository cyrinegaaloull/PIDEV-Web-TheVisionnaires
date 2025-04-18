<?php

namespace App\Controller\front_office\user;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RouteDebugController extends AbstractController
{
    #[Route('/routes', name: 'app_routes_debug')]
    public function routesDebug(): Response
    {
        $router = $this->container->get('router');
        $routes = $router->getRouteCollection();
        
        $routeList = [];
        foreach ($routes as $name => $route) {
            $methods = $route->getMethods() ? implode(', ', $route->getMethods()) : 'ANY';
            $routeList[] = [
                'name' => $name,
                'path' => $route->getPath(),
                'methods' => $methods
            ];
        }
        
        // Return as HTML table
        $html = '<table border="1" cellpadding="5">
                <tr>
                    <th>Route Name</th>
                    <th>Path</th>
                    <th>Methods</th>
                </tr>';
        
        foreach ($routeList as $route) {
            $html .= sprintf(
                '<tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                </tr>',
                $route['name'],
                $route['path'],
                $route['methods']
            );
        }
        
        $html .= '</table>';
        
        return new Response($html);
    }
}