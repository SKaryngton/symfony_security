<style>
green { color: #299660}
yel { color: #9ea647}
blue { color: #099fc0}
red {color: #ce4141}
</style>
# <green>Symfony security

```
docker-compose up -d
```

Next, build the database and execute the migrations with:

```
# "symfony console" is equivalent to "bin/console"
# but it's aware of your database container
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
```

**Start the Symfony web server**

```
symfony serve -d
```

**Optional: Webpack Encore Assets**

```
yarn install
yarn encore dev --watch
```

# <green>Security `symfony console debug:config security`
 
  - installation
     - <blue>composer require security


# <green>Authentication
```
Who are you? 
And can you prove it?

users        Login forms      remember me cookies      SSO's     LDAP

passwords     API keys        social Authentication(OAuth)

```


 - ### <yel>create a user class
    - <blue>symfony console make:user 
 - ### <yel>create login form
    - <blue>symfony console make:auth
      - <green>Empty authenticator
       1. <yel>Activate the authenticator in security.yaml and choose him as auth entry point
       ```
       firewalls:
         dev:
            ...
         main:
            ...
            custom_authenticator:
                - App\Security\SecurityAuthenticator
            entry_point: App\Security\LoginAuthenticator
         
       ```    
      2. <yel>when does an  user authenticate `if getPathInfo === '/login' && $request->isMethod('POST')`
       ```
        class SecurityAuthenticator extends AbstractAuthenticator
       {
            public function supports(Request $request): ?bool
            {
              return  $request->getPathInfo() === '/login' && $request->isMethod('POST');
              }

             public function authenticate(Request $request): PassportInterface
          {
       dd('authenticate');
          }
       ```
      3. <yel>how does an User authenticate with `PASSPORT: new Passport(new UserBadge(email),new PasswordCredentials(password))`<br><br>
         #### `providers`:  
           -  help to find the user in DB using email 
           -  refresh userInfo at the beginning of any request 
           - If any important information(email, password) about the user change he is automatically logout
      ```
         security.yaml
         security:
          ....
            providers:
               app_user_provider:
                     entity:
                        class: App\Entity\User
                        property: email
            firewalls:
                ...
                main:
                ...
                provider: app_user_provider
                ....
      
      
       class SecurityAuthenticator extends AbstractAuthenticator
       {      ......
             public function authenticate(Request $request): PassportInterface
          {
             $email = $request->request->get('email', '');

        
            return new Passport(
               new UserBadge($email),
               new PasswordCredentials($request->request->get('password', ''))
            ); 
          }
      
         template
          <form>
            <input type="hidden" name="_csrf_token"
           value="{{ csrf_token('authenticate') }}">
          <form>
      ```
      4. <yel>If Authentication was successfully `return NULL if API`or`redirect to a page if WEBAPP`
      ```
          class SecurityAuthenticator extends AbstractAuthenticator
           {
              private UrlGeneratorInterface $urlGenerator;

              public function __construct(UrlGeneratorInterface $urlGenerator)
              {
                 $this->urlGenerator = $urlGenerator;
              }
              public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
              {
              return new RedirectResponse($this->urlGenerator->generate('app_homepage'));
              }
           }
      ```
      5. <yel>If Authentication failed :
          - save the error with <br>
             ``` $request->getSession()->set(Security::AUTHENTICATION_ERROR,$exception);```
          - redirect to login Page <br>
            ```
               public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
               {
                 $request->getSession()->set(Security::AUTHENTICATION_ERROR,$exception);
                 return new RedirectResponse($this->urlGenerator->generate('app_login'));
                }
            ```
          - the login page is rendered with the Data `error` who the last authentictionerror ist.
            ```
                class SecurityController extends AbstractController
                 {
                  /**
                   * @Route("/login", name="app_login")
                     */
                   public function index(AuthenticationUtils $authenticationUtils): Response
                   {
                     return $this->render('security/index.html.twig',[
                        'error'=>$authenticationUtils->getLastAuthenticationError()
                     ]);
                   }
                   }
            
            
            
            
                  templates/security/index.html.twig
            
                   <form method="post">
                     {% if error %}
                      <div class="alert alert-danger">{{ error.messageKey}}</div>
                     {% endif %}
                   </form>
            ```
            - <yel>Customize error messages
              ```
                translations/security.en.yaml
                   
                 "Invalid credentials.": "Invalid email or password entered!"
              
              
              
                templates/security/index.html.twig
              
                  <form method="post">
                     {% if error %}
                      <div class="alert alert-danger">{{ error.messageKey|trans(error.messageData, 'security') }}</div>
                     {% endif %}
                   </form>
              ```
               
      6. <green>LOGOUT
         ```
         security.yaml
         
         security:
          ....
            firewalls:
                ...
                main:
                ...
                   logout:
                        path: app_logout
                        target: app_login
         
         
         
         class SecurityController extends AbstractController
         {
         .....
          /**
          *@Route("/logout", name="app_logout")
          */
          public function logout(): void
          {
           throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
          }
         }
         ```
      7. <yel>Remember me with checkbox
         
         ```
            .env
         
                APP_SECRET=c28f3d37eba278748f3c0427b313e86a
         
         
            
            security.yaml
         
                security:
                      .....
                    firewalls:
                      ....
                         main:
                          .....
                            remember_me:
                                 secret: '%kernel.secret%'
                                 lifetime: 2592000           #30 jours
         
         
         
         
            class SecurityAuthenticator extends AbstractAuthenticator
            {      ......
               public function authenticate(Request $request): PassportInterface
               {
                 .....


            return new Passport(
               new UserBadge($email),
               new PasswordCredentials($request->request->get('password', '')),
               [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
         
         
                                          new RememberMeBadge()
               ]
              ); 
            }
         ```
      8. <yel>Remember me without checkbox
          -  Option 1 (work even if important user  data change)
             ```
              .env
         
                    APP_SECRET=c28f3d37eba278748f3c0427b313e86a



              security.yaml

                security:
                      .....
                    firewalls:
                      ....
                         main:
                          .....
                            remember_me:
                                 secret: '%kernel.secret%'
                                 always_remember_me: true
                      
             ```
         - Option 2 (checking user password if remember me is activated )
              ```
              .env
         
                    APP_SECRET=c28f3d37eba278748f3c0427b313e86a



              security.yaml

                security:
                      .....
                    firewalls:
                      ....
                         main:
                          .....
                            remember_me:
                                 secret: '%kernel.secret%'
                                 always_remember_me: true OR lifetime: 2592000
                                 signature_properties: [password]
                      
             ```
         9. <yel>login_throttling</yel> :Prevent someone from a single API adresse to test password over and Over 
             - <blue>composer require symfony/rate-limiter
             - ```
               security.yaml

                security:
                      .....
                    firewalls:
                      ....
                         main:
                            .....
             
                            login_throttling: true
                            OR
                            login_throttling: 
                                  max_attempts: 3 //default 5
                                  interval: '1 minute'
               ```
         10. <yel>redirect anonymous user to Entry Point(LoginPage)
             ```
                 class LoginAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
                {
                 public function start(Request $request, AuthenticationException $authException = null): Response
                    {
                      return new RedirectResponse($this->urlGenerator->generate('app_login'));
                    }
                 }
             ```
         11. <yel>redirect to previous URL
             ```
             class LoginAuthenticator extends AbstractLoginFormAuthenticator
             {
                   use TargetPathTrait;

     
                  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
                 {
                  if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
                   return new RedirectResponse($targetPath);
                  }
                  .....
              }

             ```
         12. <yel>Reduce code replace function start(), onAuthenticationfailure and Support()   by  `getLoginUrl()`
             ```
                class LoginAuthenticator extends AbstractLoginFormAuthenticator
               {
                  use TargetPathTrait;

                  public const LOGIN_ROUTE = 'app_login';

                  public function __construct(private UrlGeneratorInterface $urlGenerator)
                 {
                 }

                 public function authenticate(Request $request): Passport
                 {
                 $email = $request->request->get('email', '');

                 $request->getSession()->set(Security::LAST_USERNAME, $email);

                 return new Passport(
                 new UserBadge($email),
                  new PasswordCredentials($request->request->get('password', '')),
                 [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge()
                ]
               );
               }

               public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
              {
               if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
               return new RedirectResponse($targetPath);
               }


                return new RedirectResponse($this->urlGenerator->generate('app_cheeses'));

                  }

              protected function getLoginUrl(Request $request): string
              {
               return $this->urlGenerator->generate(self::LOGIN_ROUTE);
               }
                }
             ```
         13. Use form_login from Symfony
             - See default parameters <blue>symfony console debug:config security 
              ```
              security:
                   firewalls:
                       
                       main:   
                        entry_point: App\Security\LoginAuthenticator
                        form_login:
                             login_path: app_login
                             check_path: app_login
                             username_parameter: email
                             password_parameter: password
                             enable_csrf: true
              ```
         14. 
# <green>Authorization
```
Should you have access to this resource? 

URLs        controllers

```
 - <yel>Denying Access
     - access control
      ```
         security:
            .......
            access_control:
               - { path: ^/api, roles: ROLE_USER }
             
      ```
    
     - make an exception with PUBLIC_ACCESS ROLE
         ```
         security:
            .......
            access_control:
                 - { path: ^/api/login, roles: PUBLIC_ACCESS }
                 - { path: ^/api, roles: ROLE_USER }
         ```
     - in controller
      ```
          #[Route('/new', name: 'app_cheese_new', methods: ['GET', 'POST'])]
         public function new(Request $request, CheeseListingRepository $cheeseListingRepository): Response
         {
                $this->denyAccessUnlessGranted('ROLE_USER');
         }
   
         or
   
          #[Route('/new', name: 'app_cheese_new', methods: ['GET', 'POST'])]
         #[IsGranted("ROLE_USER")]
         public function new(Request $request, CheeseListingRepository $cheeseListingRepository): Response
         {
               
         }
      ```
     - in Twig
     ```
      <nav class="navbar navbar-expand-lg bg-light">

        <div class="container-fluid">
            <a class="navbar-brand" href="{{ path('app_cheeses') }}">Cheese</a>


             {% if is_granted('ROLE_USER') %}
                 <a class="nav-link "  href="{{ path('api_entrypoint') }}">API</a>
                 <a class="nav-link "  href="{{ path('app_logout') }}">Log Out</a>
             {% else%}
                 <a class="nav-link "  href="{{ path('app_login') }}">Log In</a>
             {% endif %}
        </div>
    </nav>
     ```
     - <yel>Official Way To check if the User is Login
         ```
             {% if is_granted('IS_AUTHENTICATED_FULLY') %}
                 <a class="nav-link "  href="{{ path('api_entrypoint') }}">API</a>
                 <a class="nav-link "  href="{{ path('app_logout') }}">Log Out</a>
             {% else%}
                 <a class="nav-link "  href="{{ path('app_login') }}">Log In</a>
             {% endif %}
         ```
     - <yel>If you use Remember cookie you should use 
        ```
             {% if is_granted('IS_AUTHENTICATED_REMEMBERED') %}
                 <a class="nav-link "  href="{{ path('api_entrypoint') }}">API</a>
                 <a class="nav-link "  href="{{ path('app_logout') }}">Log Out</a>
             {% else%}
                 <a class="nav-link "  href="{{ path('app_login') }}">Log In</a>
             {% endif %}
         ```
     - <yel>Get User datainfo from:
        - Controller  ` $this->getUser() `
        - twig        ` {{ app.user.userIdentifier }} `
        - service
          ```
              public function __construct( private Security $security)
              {
   
              }
               public function parse()
               {
                 if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                // ...
               }
                 if ($this->security->getUser()) {
                  $this->logger->info('Rendering markdown for {user}', [
                'user' => $this->security->getUser()->getUserIdentifier()
                 ]);
                }
               }
          ```
       
     - <yel>ROLE HIERARCHY
       ```
          security.yaml    
       
            security:

                role_hierarchy:
                    ROLE_DEV: [ROLE_ADMIN]
       ```
     - <yel>Impersonation:` switch_user `
       ```
          security.yaml    
       
            security:
                     role_hierarchy:
                          ROLE_DEV: [ROLE_ALLOWED_TO_SWITCH]
                  ........
                    firewalls:
                       .....
                        main:
                          switch_user: true    
       
       
       
            
       login as dev@example.com
       then in the Url searchbar add   ?_switch_user=admin@example.com
       
       now you're logged in as  admin@example.com
       
       in Twig you can see if you're impersonating someone with:
        <nav class="navbar navbar-expand-lg bg-light"  
         {{ is_granted('ROLE_PREVIOUS_ADMIN') ? 'style="bacground-color: red ! important"' }}>
        .....
       </nav>
       
       
       To exit in the Url searchbar add  ?_switch_user=_exit
       mow you're logged in as dev@example.com
       ```
     - Serializer & API Endpoint
         la methode `json` utilise le serializer et renvoi tout les getter de lisible de l'entite
         ```
               #[Route('/api/account', name:'api_account')]
               public function api_account(): Response
               {


                  return $this->json($token,200,[],['groups'=>['token:read']]);
                }
         ```

# <green>API SESSION BASE AUTHENTICATION
   
# <green>API TOKEN AUTHENTICATION

1. Traitement d'un API token
   ```
     - Un API Token est un String qui est connecte a un User dans la BD.
     - lorsque le User effectue une requete, ii est authentifier  
       au token qui se trouve dans Le Header de la requete
     -La manière dont la chaîne de jeton est liée à l'utilisateur peut être effectuée de différentes manières. 
   
              -Par exemple, vous pouvez avoir une table de base de données de jetons d'API dans laquelle chaque jeton d'API aléatoire 
               a une relation avec une ligne de la table des utilisateurs. 
               C'est simple : notre application lit la chaîne de jeton à partir d'un en-tête, trouve ce jeton d'API dans la base de données, 
               trouve le User auquel il est lié et L'authentifie en tant qu'utilisateur. 
             
              - Une autre variante est les jetons Web JSON. 
                Dans ce cas, au lieu que le jeton soit une chaîne aléatoire, 
                les informations de l'utilisateur - comme l'ID utilisateur - sont utilisées pour créer une chaîne signée. Dans ce cas, votre application lit l'en-tête, 
                vérifie la signature sur la chaîne et utilise l' id a l'intérieur de cette chaîne pour authentifier User.
   ```
   
     - <yel>Create an ApiToken Entity
        - <blue>symfony console make:entity
        - make the class immutable by removing all setters and instantiating parameters in constructor
```
#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
class ApiToken
{
public function __construct(User $user)
{
$this->token=$user->getUserIdentifier().$user->getId().strtotime(new \DateTimeImmutable());
$this->user=$user;
$this->expiresAt = new \DateTimeImmutable('+ 1hour');

    }


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\ManyToOne(inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }



    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }



    public function getUser(): ?User
    {
        return $this->user;
    }


    }
```

   -
2. Creation et distribution d' API token
         
 ```
 
         $u = unpack('H*', (string)$user->getEmail());
        $t = unpack('H*', (string)time());

        $token= array_shift($t) . bin2hex(random_bytes(32)). array_shift($u) ;
 
 ```


# <green>Utils

 - <yel>installations
    - <blue> composer require form annotations
    - fixtures
       - <blue> composer require orm-fixtures --dev
       - symfony console make:fixtures
       - composer require zenstruck/foundry --dev
       - symfony console make:factory

    - <blue> composer require encore
    - <blue> composer require knplabs/knp-time-bundle


 - <blue> symfony console make:crud  


- Start the Webpack server  <br>
    - yarn watch
- Database
   - symfony console d:d:c
   - symfony console d:m:m
   - d:f:l
