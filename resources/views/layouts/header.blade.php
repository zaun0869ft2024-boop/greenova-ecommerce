<header>

<div id="top-header">
    <div class="container">
        <div class="row">

            <div class="col-md-6">
                <span style="color:white;">
                    Welcome to Online Shop
                </span>
            </div>

            <div class="col-md-6 text-right">

                <a href="#" style="color:white;margin-right:20px;">
                    <i class="fa fa-money"></i>
                    IDR
                </a>

            @if(Auth::check())

            <a href="#" style="color:white; margin-right:20px;">
                <i class="fa fa-user"></i>
                {{ Auth::user()->name }}
            </a>


            <form action="{{ route('logout') }}" method="POST" style="display:inline;">

            @csrf

            <button type="submit" style="
            background:none;
            border:none;
            color:white;
            ">

            <i class="fa fa-sign-out"></i>
            Logout

            </button>

            </form>


            @else


            <a href="{{ route('login') }}" style="color:white; margin-right:20px;">

            <i class="fa fa-user"></i>

            Login

            </a>


            <a href="{{ route('register') }}" style="color:white;">

            Register

            </a>


            @endif

            </div>

        </div>
    </div>
</div>


<div id="header">

    <div class="container">

        <div class="row">

            <div class="col-md-3">
                <h1 class="logo">
                    Online Shop
                </h1>
            </div>


            <div class="col-md-6">

                <form class="search-box">

                    <input 
                    type="text"
                    placeholder="Search here">

                    <button>
                        <i class="fa fa-search"></i>
                        Search
                    </button>

                </form>

            </div>


            <div class="col-md-3">

                <div class="text-center">

                    <a href="{{ route('cart') }}" class="cart">

                        <i class="fa fa-shopping-cart"></i>
                        Your Cart

                    </a>

                </div>

            </div>


        </div>

    </div>

</div>

</header>