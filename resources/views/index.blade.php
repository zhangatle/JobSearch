<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>职位搜索</title>
    <link rel="stylesheet" href="{{asset("css/style.css")}}">
    <link rel="stylesheet" href="{{asset("css/index.css")}}">
</head>
<body>
<div id="container">
    <div id="bd">
        <div id="main">
            <h1 class="title">
                <a href="https://www.jianshu.com/u/db9a7a0daa1f"><div class="logo large"></div></a>
            </h1>
            <div class="nav ue-clear">
                <ul class="searchList">
                    <li class="searchItem current" data-type="article">伯乐文章</li>
                    <li class="searchItem" data-type="question">知乎问答</li>
                    <li class="searchItem" data-type="job">拉勾职位</li>
                </ul>
            </div>
            <div class="inputArea">
                <input type="text" class="searchInput" />
                <input type="button" class="searchButton" onclick="add_search()" />
                <ul class="dataList">
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                    <li></li>
                </ul>
            </div>

            <div class="historyArea">
{{--                <p class="history">--}}
{{--                    <label>热门搜索：</label>--}}
{{--                    {% for search_word in topn_search %}--}}
{{--                    <a href='/search?q={{ search_word }}&s_type=article'>{{ search_word }}</a>--}}
{{--                    {% endfor %}--}}
{{--                </p>--}}
                <p class="history mysearch">
                    <label>我的搜索：</label>
                    <span class="all-search">
                        <a href="javascript:;"></a>
                        <a href="javascript:;"></a>
                        <a href="javascript:;"></a>
                        <a href="javascript:;"></a>
                    </span>

                </p>
            </div>
        </div><!-- End of main -->
    </div><!--End of bd-->

    <div class="foot">
        <div class="wrap">
            <div class="copyright">Copyright &copy;search.zhangatle.cn 版权所有  E-mail:search@zhangatle.cn</div>
        </div>
    </div>
</div>
</body>
<script src="{{asset("js/jquery.js")}}"></script>
<script src="{{asset("js/global.js")}}"></script>
<script type="text/javascript">
    var suggest_url = "{{route("api.suggest")}}"
    var search_url = "{{route("search")}}"
    console.log(suggest_url)

    $('.searchList').on('click', '.searchItem', function(){
        $('.searchList .searchItem').removeClass('current');
        $(this).addClass('current');
    });

    function removeByValue(arr, val) {
        for(var i=0; i<arr.length; i++) {
            if(arr[i] == val) {
                arr.splice(i, 1);
                break;
            }
        }
    }
    // 搜索建议
    $(function(){
        $('.searchInput').bind(' input propertychange ',function(){
            var searchText = $(this).val();
            var tmpHtml = ""
            $.ajax({
                cache: false,
                type: 'get',
                dataType:'json',
                url:suggest_url+"?s="+searchText+"&s_type="+$(".searchItem.current").attr('data-type'),
                async: true,
                success: function(data) {
                    for (var i=0;i<data.length;i++){
                        tmpHtml += '<li><a href="'+search_url+'?q='+data[i].slice(0,35)+'&s_type='+$(".searchItem.current").attr('data-type')+'">'+data[i]+'</a></li>'
                    }
                    $(".dataList").html("")
                    $(".dataList").append(tmpHtml);
                    if (data.length == 0){
                        $('.dataList').hide()
                    }else {
                        $('.dataList').show()
                    }
                },
                error: function (aa) {
                    console.log(aa)
                }
            });
        } );
    })

    hideElement($('.dataList'), $('.searchInput'));

</script>
<script>
    var searchArr;
    //定义一个search的，判断浏览器有无数据存储（搜索历史）
    if(localStorage.search){
        //如果有，转换成 数组的形式存放到searchArr的数组里（localStorage以字符串的形式存储，所以要把它转换成数组的形式）
        searchArr= localStorage.search.split(",")
    }else{
        //如果没有，则定义searchArr为一个空的数组
        searchArr = [];
    }
    //把存储的数据显示出来作为搜索历史
    MapSearchArr();

    function add_search(){
        var val = $(".searchInput").val();
        if (val.length>=2){
            //点击搜索按钮时，去重
            KillRepeat(val);
            //去重后把数组存储到浏览器localStorage
            localStorage.search = searchArr;
            //然后再把搜索内容显示出来
            MapSearchArr();
        }

        window.location.href=search_url+'?q='+val+"&s_type="+$(".searchItem.current").attr('data-type')

    }

    function MapSearchArr(){
        var tmpHtml = "";
        var arrLen = 0
        if (searchArr.length >= 5){
            arrLen = 5
        }else {
            arrLen = searchArr.length
        }
        for (var i=0;i<arrLen;i++){
            tmpHtml += '<a href="'+search_url+'?q='+searchArr[i]+"&s_type="+$(".searchItem.current").attr('data-type')+'">'+searchArr[i]+'</a>'
        }
        $(".mysearch .all-search").html(tmpHtml);
    }
    //去重
    function KillRepeat(val){
        var kill = 0;
        for (var i=0;i<searchArr.length;i++){
            if(val===searchArr[i]){
                kill ++;
            }
        }
        if(kill<1){
            searchArr.unshift(val);
        }else {
            removeByValue(searchArr, val)
            searchArr.unshift(val)
        }
    }


</script>
</html>
