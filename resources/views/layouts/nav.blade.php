<style>
    header {
        background-color: #3490dc;
        height: 50px;
        margin-bottom: 10px;
    }

    #search_form {
        margin: 0 35%;
        width: 30%;
        padding: 10px;
        display: flex;
        flex-direction: row;
    }

    #search_field {
        background-color: white;
        -webkit-border-radius: 10px 0 0 10px;
        -moz-border-radius: 10px 0 0 10px;
        border-radius: 10px 0 0 10px;
        border: none;
        height: 30px;
        width: 80%;
        padding: 0 10px;
    }

    .search_button {
        background-color: #285f9b;
        height: 30px;
        line-height: 30px;
        width: 10%;
        color: whitesmoke;
        border: none;
        -webkit-border-radius: 0 10px 10px 0;
        -moz-border-radius: 0 10px 10px 0;
        border-radius: 0 10px 10px 0;
    }

    .search_button > i {
        margin: 3px 0;
    }

    #reset_search {
        width: 10%;
        background-color: white;
        height: 30px;
        border: none;
        padding: 3px;
        color: #e6e6e6;
    }

    #reset_search:hover {
        color: #c3c3c3;
    }
</style>

<header>
    <form action="{{ route('list') }}" method="GET" id="search_form">
        <input type="text" id="search_field" name="search" placeholder="Введите свой поисковый запрос в поле"
               value="{{ request()->get('search') }}">
        <button id="reset_search" type="reset" title="Очистить поиск" value="Reset">
            <i class="material-icons">
                close
            </i>
        </button>
        <button class="search_button">
            <i class="material-icons">
                search
            </i>
        </button>

    </form>
</header>

<script>
    let reset = document.getElementById('reset_search');
    let field = document.getElementById('search_field');
    let form = document.getElementById('search_form');

    reset.onclick = function () {
        field.value = '';
        form.submit();
    }
</script>