# ______________ MIDDLEWARE

## Introduction
Middleware cung cấp cơ chế cho việc kiểm tra và lọc các request đi vào app của bạn.
ví dụ: nếu user chưa được authenticated -> middleware sẽ redirect lại login screen ngược lại user đó sẽ được tiến sâu hơn vào app
beside authenticated, middleware còn có thể thực hiện nhiều tác vụ khác như ghi lại log của tất cả các request đến application. 1 số middleware được include trong laravel framework (ex: CSRF protection). Tất cả các middleware được nằm trong thư mục `app/Http/Middleware`

## Defining middleware
Để tạo 1 Middleware mới, ta sử dụng lệnh `make:middleware`
ex:
```php artisan make:middleware EnsureTokenIsValid```

`EnsureTokenIsValid ` class vừa tạo sẽ nằm trong thư mục `app/Http/Middleware`

1 ví dụ để kiểm tra token hợp lệ như sau:
~~~
<?php

namespace App\Http\Middleware;

use Closure;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->input('token') !== 'my-secret-token') {
            return redirect('home');
            //Nếu token không đúng sẽ bị redirect lại trang home
        }
        //ngược lại sẽ được thực hiện các tác vụ nào đó mà ta định nghĩa
        return $next($request);
    }
}
~~~
->| Tất cả middleware được resolved thông qua `service container`, nên bạn có thể type-hint một vài dependencies bạn cần trong middleware's constructor.

## ## Middleware & Responses
Middleware có thể thực hiện nhiều tasks trước hoặc sau khi passing request vào application sâu hơn
ex: 
~~~
// before
<?php

namespace App\Http\Middleware;

use Closure;

class BeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        // Perform action

        return $next($request);
    }
}
~~~
~~~
// after
namespace App\Http\Middleware;

use Closure;

class AfterMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Perform action

        return $response;
    }
}
~~~

## ## Registering Middleware
### ### Global Middleware
Nếu bạn muốn middleware running trong tất cả mọi nơi trong application, thì bạn nên đặt list middleware class trong `$middleware` trong thư mục `app/Http/Kernel.php`
### ### Assigning Middleware To Routes
Nếu bạn muốn assign middleware cho những route riêng, trước tiên bạn nên gán cho middleware 1 key tại (`$routeMiddleware`) trong file `app/Http/Kernel.php`  
ex:
~~~
// Within App\Http\Kernel class...

protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
    'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
];
~~~
khi middleware đã được xác định trong kernel thì bạn có thể gán nó vào routes mà bạn muốn
~~~
Route::get('/profile', function () {
    //
})->middleware('auth');
~~~
or assign multiple middleware cho route
~~~
Route::get('/', function () {
    //
})->middleware(['first', 'second']);
~~~
or dùng tên class name
~~~
use App\Http\Middleware\EnsureTokenIsValid;

Route::get('/profile', function () {
    //
})->middleware(EnsureTokenIsValid::class);
~~~
Khi assign middleware cho 1 nhóm routes, thỉnh thoảng ta cần ngăn middleware cho 1 số route con trong group ta có thể sử dụng `withoutMiddleware` method
~~~
use App\Http\Middleware\EnsureTokenIsValid;

Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::get('/', function () {
        //
    });

    Route::get('/profile', function () {
        //
    })->withoutMiddleware([EnsureTokenIsValid::class]);
});
~~~
`withoutMiddleware` chỉ có thể remove route middleware và nó không được apply cho `global middleware`

### ### Middleware Groups
Thỉnh thoảng, bạn muốn group 1 số middleware dưới 1 key xác định để dễ assign chúng cho những route bạn muốn, bạn có thể thực hiện điều này khi sử dụng `$middlewareGroups ` trong kernel.php
Ngoài ra, laravel còn cung cấp `web` và `api` group chứa các middleware phổ biến mà bạn muốn áp dụng cho các route `web` hoặc `api` của application
~~~
/**
 * The application's route middleware groups.
 *
 * @var array
 */
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
~~~
middleware group có thể assign cho các routes và controller action bằng cách sử dụng cú pháp tương tự individual midlleware
~~~
Route::get('/', function () {
    //
})->middleware('web');

Route::middleware(['web'])->group(function () {
    //
});
~~~
*Ngoài ra, `web` và `api` middleware group sẽ tự động apply cho file `route/web.php` và `route/api.php` bởi `App\Providers\RouteServiceProvider.`*

### ### Sorting Middleware
Nếu bạn cần chỉ định xem middleware nào sẽ chạy trước, middleware nào chạy sao, thì bạn chỉ cần sắp xếp chúng theo thứ tự trừ trên xuống dưới trong protected $middlewarePriority ở file app/Http/Kernel.php.
~~~
/**
 * The priority-sorted list of middleware.
 *
 * This forces non-global middleware to always be in the given order.
 *
 * @var array
 */
protected $middlewarePriority = [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class,
    \Illuminate\Session\Middleware\AuthenticateSession::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \Illuminate\Auth\Middleware\Authorize::class,
];
~~~

### ### Middleware Parameters
middleware cũng có thể nhận các param được truyền vào, ví dụ nếu bạn muốn xác thực 1 user nào đó cần editor để được quyền đi sâu hơn vào application thực hiện chức năng nào đó ta có thể làm như sau:
~~~
<?php

namespace App\Http\Middleware;

use Closure;

class EnsureUserHasRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (! $request->user()->hasRole($role)) {
            // Redirect...
        }

        return $next($request);
    }

}
~~~
middleware có thể được gọi như sau:
~~~
Route::put('/post/{id}', function ($id) {
    //
})->middleware('role:editor');
<!-- tên và param sẽ được ngăn cách bởi dấu `:`, nếu muốn truyền nhiều param ta sẽ ngăn cách các param đó bằng dấu `,` -->
~~~

### ### Terminable Middleware
Đôi lúc bạn cần xử lý 1 số công việc sau khi HTTP response đã được gửi đến trình duyệt thì bạn có thể sử dụng terminate method. phương thức này sẽ tự động được gọi sau khi response đã được gửi cho browser
~~~
<?php

namespace Illuminate\Session\Middleware;

use Closure;

class TerminatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // ...
    }
}
~~~
terminate method nhận 2 tham số là request và response, khi bạn có difined terminate middleware, bạn nên add nó cho list của route hoặc global middleware tại `app/Http/Kernel.php`
Khi gọi terminate, laravel sẽ resolve 1 instance của middleware từ service container, nếu bạn muốn sử dụng same middleware instance khi handle và terminate được gọi thfi bạn nên register middleware với `singleton` method,
thông thường điều này nên được đăng lí trong `AppServiceProvider`
~~~
use App\Http\Middleware\TerminatingMiddleware;

/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    $this->app->singleton(TerminatingMiddleware::class);
    // cần xem thêm
}
~~~