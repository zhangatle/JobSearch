<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{env("app_name")}}-搜索结果</title>
    <link rel="stylesheet" href="{{asset("css/style.css")}}">
    <link rel="stylesheet" href="{{asset("css/result.css")}}">
</head>
<body>
<div id="container">
    <div id="hd" class="ue-clear">
        <a href="{{route("index")}}">
            <div class="logo"></div>
        </a>
        <div class="inputArea">
            <input type="text" class="searchInput" placeholder="请输入关键词进行搜索" value="{{$key_words}}"/>
            <input type="button" class="searchButton" onclick="add_search()"/>
        </div>
    </div>
    <div id="bd" class="ue-clear">
        <div id="main">
            <div class="sideBar">
                <div class="subfield">实时已爬取数据统计</div>
                <ul class="subfieldContext">
                    <li>
                        <span class="name">微信抓取</span>
                    </li>
                </ul>
                <div class="sideBarShowHide">
                    <a href="javascript:;" class="icon"></a>
                </div>
            </div>
            <div class="resultArea">
                <p class="resultTotal">
                    <span class="info">找到约&nbsp;<span class="totalResult">{{ $total }}</span>&nbsp;条结果(用时<span class="time">{{ $last_seconds }}</span>秒)，共约<span class="totalPage">{{ $page_nums }}</span>页</span>
                </p>
                <div class="resultList">
                    <div class="resultItem">
                        @foreach($hit_list as $hits)
                            <div class="itemHead">
                                <span class="fileType">
                                <span class="label">来源：</span>
                                <span class="value">{{$hits["nickname"]}}({{$hits["wxid"]}})</span>
                            </span>
                                <span class="dependValue">
                                <span class="label">得分：</span>
                                <span class="value">{{ $hits['score'] }}</span>
                            </span>
                            </div>
                            <div class="itemBody">{!! $hits['content'] !!}</div>
                            <div class="itemFoot">
                            <span class="info">
                                <label>发送人：</label>
                                <span class="value">{{$hits["message_sender"]}}</span>
                            </span>
                                <span class="info">
                                <label>群昵称：</label>
                                <span class="value">{{$hits["message_group"]}}</span>
                            </span>
                            <span class="info">
                            <label>发布时间：</label>
                            <span class="value">
                             {{ $hits['create_date'] }}
                            </span>
                            </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!-- 分页 -->
                <div class="pagination ue-clear"></div>
                <!-- 相关搜索 -->
            </div>
            <div class="historyArea">
                <div class="hotSearch">
                    <h6>热门搜索</h6>
                    <ul class="historyList">
                        @foreach($top_search as $key => $search)
                            <li><a href="/search?q={{ $search }}&s_type=article">{{$key + 1}}:{{ $search }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="mySearch">
                    <h6>我的搜索</h6>
                    <ul class="historyList">
                    </ul>
                </div>
            </div>
        </div><!-- End of main -->
    </div><!--End of bd-->
</div>
<div id="foot">Copyright &copy;{{env("APP_NAME")}} 版权所有 E-mail:search@dataai.cn</div>
</body>
<script src="{{asset("js/jquery.js")}}"></script>
<script src="{{asset("js/global.js")}}"></script>
<script src="{{asset("js/pagination.js")}}"></script>
<script type="text/javascript">
    let search_url = "{{route("search")}}"
    $('.searchList').on('click', '.searchItem', function () {
        $('.searchList .searchItem').removeClass('current');
        $(this).addClass('current');
    });

    $.each($('.subfieldContext'), function (i, item) {
        $(this).find('li:gt(2)').hide().end().find('li:last').show();
    });

    function removeByValue(arr, val) {
        for (let i = 0; i < arr.length; i++) {
            if (arr[i] === val) {
                arr.splice(i, 1);
                break;
            }
        }
    }

    $('.subfieldContext .more').click(function (e) {
        let $more = $(this).parent('.subfieldContext').find('.more');
        if ($more.hasClass('show')) {
            if ($(this).hasClass('define')) {
                $(this).parent('.subfieldContext').find('.more').removeClass('show').find('.text').text('自定义');
            } else {
                $(this).parent('.subfieldContext').find('.more').removeClass('show').find('.text').text('更多');
            }
            $(this).parent('.subfieldContext').find('li:gt(2)').hide().end().find('li:last').show();
        } else {
            $(this).parent('.subfieldContext').find('.more').addClass('show').find('.text').text('收起');
            $(this).parent('.subfieldContext').find('li:gt(2)').show();
        }

    });

    $('.sideBarShowHide a').click(function (e) {
        if ($('#main').hasClass('sideBarHide')) {
            $('#main').removeClass('sideBarHide');
            $('#container').removeClass('sideBarHide');
        } else {
            $('#main').addClass('sideBarHide');
            $('#container').addClass('sideBarHide');
        }

    });
    let key_words = "{{ $key_words }}"
    //分页
    $(".pagination").pagination({{ $total }}, {
        current_page: {{ $page - 1}}, //当前页码
        items_per_page: 10,
        display_msg: true,
        callback: pageselectCallback
    });

    function pageselectCallback(page_id, jq) {
        window.location.href = search_url + '?q=' + key_words + '&p=' + (page_id + 1) + "&s_type=" + $(".searchItem.current").attr('data-type')
    }

    setHeight();
    $(window).resize(function () {
        setHeight();
    });

    function setHeight() {
        if ($('#container').outerHeight() < $(window).height()) {
            $('#container').height($(window).height() - 33);
        }
    }
</script>
<script type="text/javascript">
    $('.searchList').on('click', '.searchItem', function () {
        $('.searchList .searchItem').removeClass('current');
        $(this).addClass('current');
    });

    // 联想下拉显示隐藏
    $('.searchInput').on('focus', function () {
        $('.dataList').show()
    });

    // 联想下拉点击
    $('.dataList').on('click', 'li', function () {
        let text = $(this).text();
        $('.searchInput').val(text);
        $('.dataList').hide()
    });

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
        if(val.length > 30) {
            val = val.substring(0, 30)+"..."
        }
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
        if (searchArr.length > 6) {
            arrLen = 6
        } else {
            arrLen = searchArr.length
        }
        for (let i = 0; i < arrLen; i++) {
            tmpHtml += '<li><a href="/search?q=' + searchArr[i] + '&s_type=' + $(".searchItem.current").attr('data-type') + '">' + searchArr[i] + '</a></li>'
        }
        $(".mySearch .historyList").append(tmpHtml);
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
