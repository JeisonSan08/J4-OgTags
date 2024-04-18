<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text; // Atualizado para Joomla 4

class PlgSystemOgtags extends CMSPlugin
{
 public function onBeforeCompileHead()
{
    $app = Factory::getApplication();
    $doc = Factory::getDocument();

    if ($app->isClient('administrator') || $doc->getType() !== 'html') {
        return;
    }

    $option = $app->input->getCmd('option', '');
    $view = $app->input->getCmd('view', '');
    if ($option === 'com_content' && $view === 'article') {
        $id = $app->input->getInt('id');
        if ($id) {
            $article = $this->getArticle($id);
            
                            // URL Canônica
$canonicalUrl = Uri::getInstance()->toString(array('scheme', 'host', 'port', 'path'));
$doc->addCustomTag('<meta property="og:type" content="article" />');
$doc->addCustomTag('<meta property="og:url" content="' . $canonicalUrl . '" />');


            if ($article) {
                
                 // Título
                if (!empty($article->title)) {
                    $doc->addCustomTag('<meta property="og:title" content="' . htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8') . '" />');
                      $doc->addCustomTag('<meta property="og:image:alt" content="' . htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8') . '" />');
                }

                // Descrição
                // Aqui você pode decidir se quer usar introtext, fulltext ou ambos como descrição
                $description = !empty($article->introtext) ? strip_tags($article->introtext) : '';
                if (!empty($description)) {
                    // Limita o comprimento da descrição para um valor razoável, por exemplo, 150 caracteres
                    $description = substr($description, 0, 150);
                    $doc->addCustomTag('<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '" />');
                }
                // Imagem
                $images = json_decode($article->images);
                $ogImage = '';
                if (!empty($images->image_intro)) {
                    $ogImage = $images->image_intro;
                } elseif (!empty($images->image_fulltext)) {
                    $ogImage = $images->image_fulltext;
                }
                if ($ogImage) {
                    $parsedUrl = explode('#', $ogImage, 2);
                    $ogImage = $parsedUrl[0];
                    $baseUrl = rtrim(Uri::base(), '/');
                    $fullImageUrl = $baseUrl . '/' . ltrim($ogImage, '/');
                    $doc->addCustomTag('<meta property="og:image" content="' . $fullImageUrl . '" />');
                }

               


            }
        }
    }
}

   private function getArticle($id)
{
    // Obtenha uma instância do banco de dados.
    $db = Factory::getDbo();

    // Crie uma nova consulta.
    $query = $db->getQuery(true);

    // Selecione todos os campos do artigo a partir do ID fornecido.
    $query->select($db->quoteName(array('id', 'title', 'alias', 'introtext', 'fulltext', 'images')))
          ->from($db->quoteName('#__content'))
          ->where($db->quoteName('id') . ' = ' . $db->quote($id));

    // Defina a consulta no banco de dados.
    $db->setQuery($query);

    // Carregue o resultado como um objeto PHP.
    try {
        $article = $db->loadObject();

        if ($article) {
            // Se o artigo foi encontrado, retorne o objeto do artigo.
            return $article;
        }
    } catch (RuntimeException $e) {
        // Se ocorrer um erro ao buscar o artigo, você pode querer tratar ou registrar o erro aqui.
        // Por exemplo, você poderia retornar null ou um objeto vazio.
        return null;
    }

    // Se não encontrar o artigo, ou se ocorrer um erro, retorna null.
    return null;
}

}
