<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Http\Exception\NotFoundException;
use Cake\Event\Event;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 *
 * @method \App\Model\Entity\Article[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ArticlesController extends AppController
{
    /**
     * beforeFilter method
     *
     * @param Event $event
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        //$this->Auth->allow(['index', 'view', 'display']) ;
       
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        //$this->set('articles', $this->Articles->find('all'));
        $articles = $this->Articles->find('all');
        $this->set([
            'articles' => $articles,
            '_serialize' => ['articles']
        ]);
    }

    /**
     * View method
     *
     * @param string|null $id Article id.
     * @return void
     */
    public function view($id = null)
    {
        $article = $this->Articles->get($id);
        $this->set([ //compact('article'));
            'article' => $article,
            '_serialize' => ['article']
        ]);
    }

    /**
     * Add method
     *
     * @return void
     */
    public function add()
    {
        $article = $this->Articles->newEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $article->user_id = $this->Auth->user('id');
            if ($this->Articles->save($article));
        }
        $this->set([
            'article' => $article,
            '_serialize' => ['article']
        ]);
    }

    /**
     * Edit method
     *
     * @param string|null $id Article id.
     * @return void
     */
    public function edit($id = null)
    {
        $article = $this->Articles->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            $this->Articles->save($article);
            
        }
        $this->set([
            'article' => $article,
            '_serialize' => ['article']
        ]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Article id.
     * @return void
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $article = $this->Articles->get($id);
        $this->Articles->delete($article);
        $this->set([
            'success' => true,
            'article' => $article,
            '_serialize' => ['success', 'article']
        ]);
    }

    /**
     * isAuthorized method
     *
     * @param string|null $id User id.
     * @return bool Indicates whether or not the user is authorized or not
     */
    public function isAuthorized($user = null): bool {
        // All registered users can add articles
        if ($this->request->getParam('action') === 'add') {
            return true;
        }

        // The owner of an article can edit and delete it
        if (in_array($this->request->getParam('action'), ['edit', 'delete'])) {
            $articleId = (int)$this->request->getParam('pass.0');
            if ($this->Articles->isOwnedBy($articleId, $user['id'])) {
                return true;
            }
        }

        return parent::isAuthorized($user);
    }

}