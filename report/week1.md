# ___________ LARAVEL ROUTING 

## Basic Routing

### The Default Route Files
Tất cả Laravel routes sẽ được tự động load bởi `App\Providers\RouteServiceProvider`. `routes/web.php` sẽ xác định các tuyến cho giao diện web của project. các route được gán nhóm `middleware`, nhóm này cung cấp các tính năng trạng thái phiên và bảo vệ CSRF, những route trong file `routes/api.php` là 1 stateless được gán vào nhóm middleware api (những route trong nhóm này sẽ tự động được add thêm tiền tố `/api`, nếu muốn thay đổi ta có thể thay đổi trong class `RouteServiceProvider`).
Hầu hết các ứng dụng sẽ bắt đầu định nghĩa route trong file `routes/web.php`.

### Available Router Methods
~~~
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
~~~
ta cũng có thể dùng nhiều HTTP verbs:
~~~
Route::match(['get', 'post'], '/', function () {
    //
});
~~~
hoặc bất kì 1 http nào:
~~~
    Route::any('/', function () {
        //
    });
~~~
### Dependency Injection
~~~
    use Illuminate\Http\Request;

    Route::get('/users', function (Request $request) {
        // ...
    });
~~~
ta có thể type-hint `Illuminate\Http\Request` class để current HTTP request được tự động đưa vào route callback

### CSRF Protection
Một số HTML forms sử dụng method POST, PUSH, PATCH, DELETE sẽ phải bao gồm CSRF token khi gửi request. nếu không thì request đó sẽ bị rejected.

Đọc thêm:
(CSRF) Cross-site request forgery là một loại mã độc, theo đó các lệnh trái phép được thực hiện thay cho một người dùng đã xác thực.

Laravel tự động tạo ra một CSRF "token" cho mỗi người dùng hoạt động quản lý bởi ứng dụng. Mã này dùng để xác minh rằng người dùng là một trong những người dùng thực sự gửi yêu cầu đến ứng dụng.

Bất cứ khi nào ta tạo một HTML form trong ứng dụng, ta phải thêm trường CSRF token vào trong form để bảo mật CSRF middleware có thể xác nhận request. Ta có thể sử dụng csrf_field để sinh ra nó
ex: 
~~~
    <form method="POST" action="/profile">
        @csrf
        ...
    </form>
~~~
### Redirect Routes
Nếu muốn redirect URI này sang 1 URI khác tac có thể sử dụng route redirect
:ex: `Route::redirect('/here', '/there');`
default Route::redirect sẽ return 302 status code, ta cũng có thể customize the status code sử dụng third param:
ex: `Route::redirect('/here', '/there', 301);`
hoặc sử dụng Route::permanentRedirect : `Route::permanentRedirect('/here', '/there')`

### View Routes
Nếu ta muốn chuyển hướng đến trang chỉ chứa view, ta có thể sử dụng `Route::view` method, và nó chấp nhận URI là đối số thứ nhất, view name là đối số thứ 2, ngoài ra nó cũng chấp nhận 1 array data làm đối số thứ 3:
ex:
~~~
    Route::view('/welcome', 'welcome');

    Route::view('/welcome', 'welcome', ['name' => 'Taylor']);
~~~

-------------
# Route Parameters
## required param
trong 1 số trường hợp ta cần truyền vào nó 1 param như ID cho URI, ta có thể làm như sau:
~~~
    Route::get('/user/{id}', function ($id) {
        return 'User '.$id;
    });
~~~
hoặc ta cũng có thể truyền vào 1 nhìu param:
~~~
    Route::get('/posts/{post}/comments/{comment}', function ($postId, $commentId) {
        //
    });
~~~
Những param này sẽ luôn được bọc trong `{}`và có chấp nhận dấu `_`

## Parameters & Dependency Injection
Nếu route có dependencies và muốn Laravel service container tự động inject vào route's callback, thì ta nên đặt những param phía sau dependencies:
~~~
    use Illuminate\Http\Request;

    Route::get('/user/{id}', function (Request $request, $id) {
        return 'User '.$id;
    });
~~~

## Optional Parameters
Cũng giống như `required param` nhưng param ở đây ta không cần bắt buộc truyền vào cho nó, ta dùng ? phía sau param, phải đảm bảo có 1 default value cho route tương ứng:
~~~
Route::get('/user/{name?}', function ($name = null) {
    return $name;
});

Route::get('/user/{name?}', function ($name = 'John') {
    return $name;
});
~~~

## Regular Expression Constraints
Ta có thể ràng buộc format của param bằng cách sử dụng `where` method, method này nhận tên của param và 1 regex để xác định param đúng format hay chưa:
~~~
    Route::get('/user/{name}', function ($name) {
        //
    })->where('name', '[A-Za-z]+');

    Route::get('/user/{id}', function ($id) {
        //
    })->where('id', '[0-9]+');

    Route::get('/user/{id}/{name}', function ($id, $name) {
        //
    })->where(['id' => '[0-9]+', 'name' => '[a-z]+']);
~~~
Để thuận tiện, 1 số Regex được hỗ trợ cho phép chúng ta nhanh chóng thêm các ràng buộc vào route:
~~~
    Route::get('/user/{id}/{name}', function ($id, $name) {
        //
    })->whereNumber('id')->whereAlpha('name');

    Route::get('/user/{name}', function ($name) {
        //
    })->whereAlphaNumeric('name');

    Route::get('/user/{id}', function ($id) {
        //
    })->whereUuid('id');
~~~
-> nếu Route không khớp với regex thì 1 404 HTTP response sẽ được trả về

## Global Constraints
Nếu muốn 1 router param luôn luôn bị ràng buộc bởi regex, chúng ta có thể define trong boot method của `App\Providers\RouteServiceProvider` class:
~~~
    /**
    * Define your route model bindings, pattern filters, etc.
    *
    * @return void
    */
    public function boot()
    {
        Route::pattern('id', '[0-9]+');
    }

    Route::get('/user/{id}', function ($id) {
        // chỉ được thực thi khi id là số
    });
~~~

## Encoded Forward Slashes
Ví dụ ta có route:
~~~
    Route::get('search/{search}', function ($search) {
        return $search;
    });
~~~
và cần search 1 bài viết có name là ABC/123 thì ta phải vào link như sau: `/search/ABC/123` nhưng dấu / cuối cùng sẽ bị hiểu nhầm là 1 link khác.
để khắc phục điều này Laravel đã ràng buộc param chứa dấu `/` với pattern là `.*` ex:
~~~
Route::get('/search/{search}', function ($search) {
    return $search;
})->where('search', '.*');
~~~

## Named Routes
Thay vì nhớ các URI của từng route thì ta có thể đặt tên cho nó để dễ dàng tương tác bằng phương thức name. và dùng method route('name') để chuyển hướng nó:
~~~
Route::get('home', function () {
    //
})->name('home');

return redirect()->route('home'); // chuyển hướng
~~~

Nếu route được đặt tên có chứa tham số thì ta có thể truyền tham số như sau:
~~~
Route::get('profile/{id}', function ($id) {
    //
})->name('profile');

$url = route('profile', ['id' => 1]); // /profile/1
~~~

## Inspecting The Current Route
để kiểm tra route hiện tại ta có thể dùng `named` method:
~~~
    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle($request, Closure $next)
    {
        if ($request->route()->named('profile')) {
            //
        }

        return $next($request);
    }
~~~

------------------------------

# Route Groups
Các route nằm trong cùng một nhóm sẽ được chia sẻ các thuộc tính route như namespace, middleware, tên prefix, url prefix...
##  Middleware
Để gán middleware cho các route chung một nhóm, ta có thể sử dụng phương thức middleware để lồng các route con
~~~
Route::middleware(['first', 'second'])->group(function () {
    Route::get('/', function () {
        // Uses first & second middleware...
    });

    Route::get('/user/profile', function () {
        // Uses first & second middleware...
    });
});
~~~

## Subdomain Routing
Nhóm route có thể được sử dụng để xử lý các routing tên miền con. Tên miền con có thể được gán thám số route như URIs, cho phép ta lấy một phần của tên miền con để sử dụng bên trong route hoặc controller. Tên miền con có thể được xác định bằng cách sử dụng từ khóadomain trong mảng thuộc tính: (e chưa hiểu nó ứng dụng như thế nào)
~~~
Route::domain('{account}.myapp.com')->group(function () {
    Route::get('user/{id}', function ($account, $id) {
        //
    });
});
~~~

## Route Prefixes
Phương thức này sẽ tạo 1 prefix trước các route con của nó:
ex:
~~~
Route::prefix('admin')->group(function () {
    Route::get('/users', function () {
        // Matches The "/admin/users" URL
    });
});
~~~

## Route Name Prefixes
Phương thức này tự động thêm prefix trước name cua route:
ex:
~~~
Route::name('admin.')->group(function () {
    Route::get('/users', function () {
        // Route assigned name "admin.users"...
    })->name('users');
});

=> route('admin.user');
~~~
# Route Model Binding 
Khi inject một model instance theo ID nào đó vào route hoặc controller action, thông thường ta sẽ phải truy vấn đến model theo ID đã cho. Nhưng Laravel route model binding cung cấp cho chúng ta một cú pháp thoải mái để có thể tự động inject các model object trong route. Tức là thay vì chỉ inject ID của User rồi mới khởi tạo model thì ta sẽ inject luôn cả model object thông qua ID nhận từ tham số URI.
## Implicit binding
Laravel sẽ tự động resolve model được định nghĩa trong route hoặc controller action bằng cách type-hint và khai báo biến có tên trùng với tên tham số.
~~~
Route::get('api/users/{user}', function (App\User $user) {
    return $user->email; // trả về email nếu tìm thấy user trong DB
});
~~~
Đoạn code trên nêu DB user k có kêt quả nào có id = {user} thì 1 404 HTTP response sẽ được trả về. 

## Customizing The Key
Thỉnh thoảng chúng ta muốn resolve Eloquent models sử dụng 1 cột khác ID ta làm như sau:
~~~
use App\Models\Post;

Route::get('/posts/{post:slug}', function (Post $post) {
    return $post;
});
~~~
Mặc định thì route model binding sẽ dùng ID để truy vấn vào database. Ta có thể thay đổi thiết lập này bằng cách khai báo method getRouteKeyName trong model muốn thay đổi.
~~~
/**
 * Get the route key for the model.
 *
 * @return string
 */
public function getRouteKeyName()
{
    return 'username';
}
~~~

## Custom Keys & Scoping
~~~
use App\Models\Post;
use App\Models\User;

Route::get('/users/{user}/posts/{post:slug}', function (User $user, Post $post) {
    return $post;
});
~~~
Khi implicitly binding multiple Eloquent models vào single route, route sẽ dựa trên relationship được định nghĩa trong model để return giá trị được so khớp với dữ liệu của 2 bảng trong DB

## Customizing Missing Model Behavior
Thông thường, 1 404 HTTP response sẽ được tạo khi không tìm thấy mô hình liên kết. tuy nhiên, ta có thể customize sử dụng missing method khi xác định route.
method này chấp nhận closure sẻ được gọi nếu không thể tìm thấy 1 implicitly bound model
~~~
use App\Http\Controllers\LocationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

Route::get('/locations/{location:slug}', [LocationsController::class, 'show'])
        ->name('locations.view')
        ->missing(function (Request $request) {
            return Redirect::route('locations.index');
        });
~~~

## Explitcit binding
Nếu muốn code trở nên rõ ràng,ta có thể sử dụng explitcit binding trong RouteServiceProvider tại boot bằng cách sử dụng method Route::model
~~~
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Define your route model bindings, pattern filters, etc.
 *
 * @return void
 */
public function boot()
{
    Route::model('user', User::class);

    // ...
}
~~~
sau đó inject như implitcit binding:
~~~
Route::get('profile/{user}', function (App\User $user) {
    // 
});
~~~
Nếu muốn sử dụng cách xử lý logic riêng, chúng ta có thể sử dụng phương thức Route::bind. Closure object được truyền vào sẽ nhận giá trị của tham số trên URI và sẽ trả về model object cần để inject nếu thỏa mãn điều kiện đưa ra. method này được viết trong func `boot` của `RouteServiceProvider.php`
~~~
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Define your route model bindings, pattern filters, etc.
 *
 * @return void
 */
public function boot()
{
    Route::bind('user', function ($value) {
        return User::where('name', $value)->firstOrFail();
    });

    // ...
}
~~~
Ngoài ra nếu không muốn code quá nhiều trong RouteServiceProvider, chúng ta có thể định nghĩa xử lý logic riêng này vào model class mà chúng ta muốn thông qua method resolveRouteBinding.
~~~
/**
 * Retrieve the model for a bound value.
 *
 * @param  mixed  $value
 * @param  string|null  $field
 * @return \Illuminate\Database\Eloquent\Model|null
 */
public function resolveRouteBinding($value, $field = null)
{
    return $this->where('name', $value)->firstOrFail();
}
~~~

----------------------------
# Fallback Routes
Với fallback route này, bạn có thể thực hiện một xử lý nào đó khi không có bất kì route nào thỏa mãn với request, thường thì sử dụng để báo lỗi 404 và xử lý thêm vài công việc nào đó.
~~~
Route::fallback(function () {
    //
});
~~~
Lưu ý: Fallback route phải được định nghĩa cuối cùng, sau cả các route mã hóa /.

------------------------------
# Rate Limiting
## Defining Rate Limiters
cho phép hạn chế số lượng truy cập cho 1 route hoặc 1 group of routes. 
Thông thường nó được thực hiện trong `configureRateLimiting` method của `App\Providers\RouteServiceProvider` class.
Rate limit sử dụng `RateLimiter` facade's `for` method. `for` method chấp nhận rate limit name và 1 closure và sẽ trã về cấu hình limit sẽ áp dụng cho các route. Limit configuration là 1 instances của `Illuminate\Cache\RateLimiting\Limit` class. Lớp này chứa `builder` method hữu ích để nhanh chóng xác định limit
~~~
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Configure the rate limiters for the application.
 *
 * @return void
 */
protected function configureRateLimiting()
{
    RateLimiter::for('authentication', function (Request $request) {
        return Limit::perMinute(1000);
    });
}

-> có thể gọi config này thông qua middleware của route
ex: Route::get('/login')->middleware(['throttle:global']);
~~~
Nếu những yêu cầu đến vượt quá giới hạn quy định thì 1 response 429 sẽ được return.
Nếu muốn custom response ta có thể sử dụng `response` method:
~~~
RateLimiter::for('global', function (Request $request) {
    return Limit::perMinute(1000)->response(function () {
        return response('Custom response...', 429);
    });
});
~~~
vì rate limit callbacks nhận được HTTP request instances nên ta có thể build Rate limit phù hợp dựa trên các request hoặc authenticated user:
~~~
RateLimiter::for('uploads', function (Request $request) {
    return $request->user()->vipCustomer()
                ? Limit::none()
                : Limit::perMinute(100);
});
~~~
### Segmenting Rate Limits
ví dụ ta muốn cho phép users truy cập 1 tuyến nhất định 100 lần 1 phút tên mỗi IP address, để đạt được điều này ta có thể dùng `by` method khi building rate limit:
~~~
RateLimiter::for('uploads', function (Request $request) {
    return $request->user()->vipCustomer()
                ? Limit::none()
                : Limit::perMinute(100)->by($request->ip());
});
~~~
### Multiple Rate Limits
ta cũng có thể sử dụng 1 array rate limits cho rate limit configuration.
Mỗi rate mimit sẽ được đánh giá cho tuyến đường dựa trên thứ tự chúng được đặt trong mảng:
~~~
RateLimiter::for('login', function (Request $request) {
    return [
        Limit::perMinute(500),
        Limit::perMinute(3)->by($request->input('email')),
    ];
});
~~~

## Attaching Rate Limiters To Routes
Ta có thể attaching rate limit vào các route sử dụng middleware:
~~~
Route::middleware(['throttle:uploads'])->group(function () {
    Route::post('/audio', function () {
        //
    });

    Route::post('/video', function () {
        //
    });
});
~~~
## Throttling With Redis
Thông thường, throttle mapped tới `Illuminate\Routing\Middleware\ThrottleRequests` class. Mapping này được định nghĩa trong `App\Http\Kernel`. tuy nhiên, nếu đang sử dụng Redis làm cache driver, ta có thể thay đổi mapping này sang `Illuminate\Routing\Middleware\ThrottleRequestsWithRedis` class. class này sẽ hiệu quả hơn trong việc quản lí rate limit khi sử dụng redis:
~~~
//App\Http\Kernel
'throttle' => \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
~~~

## Form Method Spoofing
HTML form không hỗ trợ PUT, PATCH, DELETE actions. Như vậy, khi muốn định nghĩa các phương thức này ta cần add cho form 1 hidden `_method`. giá trị được gửi với trường `_method` được sử dụng làm HTTP request method:
~~~
<form action="/example" method="POST">
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
</form>
~~~
Để tiện lợi hơn, ta có thể dùng @method để thạo ra method field:
~~~
<form action="/example" method="POST">
    @method('PUT')
    @csrf
</form>
~~~

## Accessing The Current Route
Ta có thể sử dụng `current`, `currentRouteName`, `currentRouteAction ` method của route facade để truy cập thông tin về route xử lí các incomming request.
~~~
use Illuminate\Support\Facades\Route;

$route = Route::current(); // Illuminate\Routing\Route
$name = Route::currentRouteName(); // string
$action = Route::currentRouteAction(); // string
~~~

## Cross-Origin Resource Sharing (CORS)
Laravel có thể tự động trả về CORS OPTIONS HTTP requests với các giá trị được config. Tất cả các cài đặt CORS có thể được config trong configuration file `config/cors.php`, `OPTIONS` request sẽ được tự động handle bởi `HandleCors` middleware. globle middleware này có thể tìm thấy trong HTTP kernel (`App\Http\Kernel`)
ex:
~~~
// config/cors.php
 public function handle($request, Closure $next)
 {
        return $next($request)
            ->header('Access-Control-Allow-Origin', 'http://abc.example.com')
            ->header('Access-Control-Allow-Methods', '*')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Headers', 'X-CSRF-Token');
 }


//kernel
 protected $routeMiddleware = [
    ...
    \App\Http\Middleware\Cors::class,
    ...
];

web.php
'middleware' => ['cors'] 
~~~

## Route Caching
Khi deploying application lên production, ta nên tận dụng route cache của laravel để tăng hiệu năng và giảm thời gian register tất cả các route của app. ta có thể tạo ra 1 route cache bằng cách chạy:
`php artisan route:cache`
Sau khi chúng ta chạy lệnh, tất cả các route sẽ được lưu vào bộ nhớ cache. Vì vậy, khi người dùng yêu cầu những route nhất định, những route sẽ được tải từ bộ nhớ cache. tuy nhiên, nếu có những route mới được thêm vào thì người dùng sẽ không truy cập được. Chúng ta cũng có thể xóa cache bằng cách chạy lệnh sau:
`php artisan route:clear`


