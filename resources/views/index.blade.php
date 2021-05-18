<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{env("APP_NAME")}}</title>
    <link rel="stylesheet" href="{{asset("css/style.css")}}">
    <link rel="stylesheet" href="{{asset("css/index.css")}}">
</head>
<body>
<div id="container">
    <div id="bd">
        <div id="main">
            <h1 class="title">
                <a href="{{env("APP_URL")}}">
                    <div class="logo large"></div>
                </a>
            </h1>
            <div class="nav ue-clear">
                <ul class="searchList">
                    <li class="searchItem current" data-type="demand">招人</li>
                    <li class="searchItem" data-type="supply">出人</li>
                </ul>
            </div>
            <div class="inputArea">
                <input type="text" class="searchInput"/>
                <input type="button" class="searchButton" onclick="add_search()"/>
                <ul class="dataList"></ul>
            </div>

            <div class="historyArea">
                <p class="history">
                    <label>热门搜索：</label>
                    @foreach($top_search as $search_word)
                    <a href='/search?q={{ $search_word }}&s_type=article'>{{ $search_word }}</a>
                    @endforeach
                </p>
                <p class="history mysearch">
                    <label>我的搜索：</label>
                    <span class="all-search"></span>
                </p>
            </div>
        </div><!-- End of main -->
    </div><!--End of bd-->
    <div class="foot">
        <div class="wrap">
            <div class="copyright">Copyright &copy;{{env("APP_NAME")}} 版权所有 E-mail:search@dataai.cn</div>
        </div>
    </div>
</div>
</body>
<script src="{{asset("js/jquery.js")}}"></script>
<script src="{{asset("js/global.js")}}"></script>
<script type="text/javascript">
    let suggest_url = "{{route("api.suggest")}}"
    let search_url = "{{route("search")}}"

    $('.searchList').on('click', '.searchItem', function () {
        $('.searchList .searchItem').removeClass('current');
        $(this).addClass('current');
    });

    function removeByValue(arr, val) {
        for (let i = 0; i < arr.length; i++) {
            if (arr[i] === val) {
                arr.splice(i, 1);
                break;
            }
        }
    }

    // 搜索建议
    $(function () {
        $('.searchInput').bind(' input propertychange ', function () {
            let searchText = $(this).val();
            let tmpHtml = ""
            $.ajax({
                cache: false,
                type: 'get',
                dataType: 'json',
                url: suggest_url + "?s=" + searchText + "&s_type=" + $(".searchItem.current").attr('data-type'),
                async: true,
                success: function (data) {
                    for (let i = 0; i < data.length; i++) {
                        tmpHtml += '<li><a href="' + search_url + '?q=' + data[i].slice(0, 35) + '&s_type=' + $(".searchItem.current").attr('data-type') + '">' + data[i] + '</a></li>'
                    }
                    $(".dataList").html("")
                    $(".dataList").append(tmpHtml);
                    if (data.length === 0) {
                        $('.dataList').hide()
                    } else {
                        $('.dataList').show()
                    }
                },
                error: function (aa) {
                    console.log(aa)
                }
            });
        });
    })
    hideElement($('.dataList'), $('.searchInput'));
</script>
<script>
    let searchArr;
    //定义一个search的，判断浏览器有无数据存储（搜索历史）
    if (localStorage.search) {
        //如果有，转换成 数组的形式存放到searchArr的数组里（localStorage以字符串的形式存储，所以要把它转换成数组的形式）
        searchArr = localStorage.search.split(",")
    } else {
        //如果没有，则定义searchArr为一个空的数组
        searchArr = [];
    }
    //把存储的数据显示出来作为搜索历史
    MapSearchArr();

    function add_search() {
        let val = $(".searchInput").val();
        if (val.length >= 2) {
            //点击搜索按钮时，去重
            KillRepeat(val);
            //去重后把数组存储到浏览器localStorage
            localStorage.search = searchArr;
            //然后再把搜索内容显示出来
            MapSearchArr();
        }

        window.location.href = search_url + '?q=' + val + "&s_type=" + $(".searchItem.current").attr('data-type')

    }

    function MapSearchArr() {
        let tmpHtml = "";
        let arrLen = 0
        if (searchArr.length >= 5) {
            arrLen = 5
        } else {
            arrLen = searchArr.length
        }
        for (let i = 0; i < arrLen; i++) {
            tmpHtml += '<a href="' + search_url + '?q=' + searchArr[i] + "&s_type=" + $(".searchItem.current").attr('data-type') + '">' + searchArr[i] + '</a>'
        }
        $(".mysearch .all-search").html(tmpHtml);
    }

    //去重
    function KillRepeat(val) {
        let kill = 0;
        for (let i = 0; i < searchArr.length; i++) {
            if (val === searchArr[i]) {
                kill++;
            }
        }
        if (kill < 1) {
            searchArr.unshift(val);
        } else {
            removeByValue(searchArr, val)
            searchArr.unshift(val)
        }
    }
</script>
</html>
