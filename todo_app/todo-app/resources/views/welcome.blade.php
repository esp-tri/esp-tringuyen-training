<!DOCTYPE html>
<html>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{asset('/asset/css/fake-bootstrap.css')}}">
<link rel="stylesheet" href="{{asset('/asset/css/css.css')}}">

<body>
    <div class="header__top">
        <div class="navbar">
            <ul class="ul__logo">
                <li class="li">
                    Eastplayers.io
                    <div id="cursor"></div>
                </li>
            </ul>
            <ul class="ul__menu">
                <li class="active">
                    Đăng kí
                </li>
                <li>
                    Đăng nhập
                </li>
            </ul>
        </div>
    </div>

    <div class="container">
        <div class="form__container">
            <form action="/action_page.php">
                <div class="form__group">
                    <label for="fname">First name:</label><br>
                    <input type="text" id="fname" name="fname" value="John">
                </div>
                
                <div class="form__group">
                    <label for="fname">First name:</label><br>
                    <input type="text" id="fname" name="fname" value="John">
                </div>
                <button type="submit"> Register </button>
            </form> 
        </div>
        
    </div>
    
    
</body>
<script>
    const cursor = document.getElementById('cursor');
    const cursorLi = document.querySelector('.li');
    document.addEventListener("mousemove", function(e) {
        let curX = e.clientX;
        let curY = e.clientY;
        cursor.style.top = curY + 'px';
        cursor.style.left = curX + 'px';
    });
</script>

</html>