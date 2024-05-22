<?php
namespace App\Command;
use Doctrine\ORM\EntityManagerInterface; //  add the entityManagerInterface 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;  //  add SymfonyStyle dependency
use Symfony\Component\Console\Attribute\AsCommand; // add asCommand dependency 
use App\Entity\Article; //  add the Article dependency 
use Exception;  // add exception depency 


// delete the unuse Logic exception and entity Manager depency 


// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:check-article-soldout')]  
class CheckArticleSoldOutCommand extends Command
{
    // delete the unuse entity manager var 

    private EntityManagerInterface $em; // instance of entity manager interface
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            // the command description shown when running "php bin/console check-article-soldout"
            ->setDescription('Check and Update  the soldout status of the  articles')
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to update the soldout status of the articles')
        ;
    }
  
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output):int // add a int type as return here 
    {
      $io = new SymfonyStyle($input, $output); // use the symfony helper to improve output style for more clarity 

        // adding a try catch to manage and display the error that could possibly happen 
        try{
            $articles = $this->em->getRepository(Article::class)->findAll();
            
            $nb_articles = count($articles);  // change the name of var , should be "articles" because article is not defined

            $io->success("found $nb_articles articles"); 
      
            foreach($articles as $article) {

                // check the soldout state only if soldout is false at the beginning 
                  if ($article->getSoldout() === false) {  
                  
                      $result = $this->em->createQueryBuilder('article')
                        ->select('article, (COUNT(ownership) - COUNT(trees)) AS stock_remaining') 
                        ->leftJoin(
                            'article.trees',
                            'trees',
                            'WITH',
                            'article = trees.article')
                        ->leftJoin(
                            'trees.ownership',
                            'ownership',
                            'WITH',
                            'arbres = ownership.tree')
                        ->where('article.id = :id')
                        ->andWhere('(article.b2c = FALSE AND article.soldout = FALSE) or (article.b2c = TRUE AND article.soldout = FALSE)')
                        ->groupBy('article.id')
                        ->setParameter('id', $article->getId())
                        ->getQuery()
                        ->getOneOrNullResult();  
                        
                        $stockRemaining = $result['stock_remaining']; //  "stock remaining" so the total number of  tree for a type minus the number of tree that have a owner 
                      
                      if ($stockRemaining <= 0) {
                        $article->setSoldout(true);
                      }

                    // remove the setSoldout to false because  we check only when soldout is false at beginning 
                  }
                
            
          }
   
          $this->em->flush();

          $io->success("Articles sold-out status updated successfully.");  // a more verbose sucessfully message  for more clarity 

          return Command::SUCCESS;    // return this if there was no problem running the command


    }catch (Exception $e) {
      
        $io->error("Error: " . $e->getMessage());  

        return Command::FAILURE;   //  return this if some error happened during the execution

    }
    
    
      

    }
}

