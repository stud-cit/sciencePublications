<template>
    <div class="container general-block">
        <h1 class="blue-page-title">Профіль - {{ data.name }}</h1>
        <div class="page-content">
            <ul class=" list-view">
                <li class="row">
                    <div class="col-lg-3 list-item list-title">Прізвище, ім’я, по-батькові:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4 && !data.guid">
                            <input class="item-value" type="text" v-model="data.name">
                        </div>
                        <div v-else>
                            {{data.name}} <img src="/img/update.png" @click="updateCabinetInfo" :class="['update-cabinet-info', updateLoading ? 'spin' : '']" title="Оновити інформацію з особистого кабінету">
                        </div>
                    </div>
                </li>
                <li class="row">
                    <div class="col-lg-3 list-item list-title">Роль:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4 && data.guid">
                            <select v-model="data.roles_id">
                                <option
                                    v-for="(item, index) in roles"
                                    :key="index"
                                    :value="item.id"
                                >{{item.name}}</option>
                            </select>
                        </div>
                        <template v-else>
                            {{data.role.name}}
                        </template>
                    </div>
                </li>
                <li class="row" v-if="!data.categ_1">
                    <div class="col-lg-3 list-item list-title">Місце роботи:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4 && !data.guid">
                                <input class="item-value" type="text" v-model="data.job">
                        </div>
                        <div v-else>
                            {{data.job}}
                        </div>
                    </div>
                </li>
                <li class="row" v-if="(data.guid)">
                    <div class="col-lg-3 list-item list-title">Посада:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4 && !data.guid">
                            <input class="item-value" type="text" v-model="data.position">
                        </div>
                        <div v-else>
                            {{data.position}}
                        </div>
                    </div>
                </li>
                <li class="row" v-if="data.custom_divisions || data.faculty">
                    <div class="col-lg-3 list-item list-title">Інститут/факультет:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="data.custom_divisions || (authUser.roles_id == 4 && !data.guid)">
                            <select v-model="data.faculty_code" @change="getDepartmentsUser">
                                <option value=""></option>
                                <option
                                    v-for="(item, index) in divisions"
                                    :key="index"
                                    :value="item.ID_DIV"
                                >{{item.NAME_DIV}}</option>
                            </select>
                        </div>
                        <div v-else>
                            {{data.faculty}}
                        </div>
                    </div>
                </li>
                <li class="row" v-if="data.custom_divisions || (data.department && (data.faculty != data.department))">
                    <div class="col-lg-3 list-item list-title">Кафедра:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="data.custom_divisions || (authUser.roles_id == 4 && !data.guid)">
                            <select v-model="data.department_code">
                                <option value=""></option>
                                <option
                                    v-for="(item, index) in departments2"
                                    :key="index"
                                    :value="item.ID_DIV"
                                >{{item.NAME_DIV}}</option>
                            </select>
                        </div>
                        <div v-else>
                            {{data.department}}
                        </div>
                    </div>
                </li>

                <li class="row" v-if="authUser.roles_id == 4 && data.guid">
                    <div class="col-lg list-item list-text">
                        <div class="pl-3">
                            <input v-model="data.custom_divisions" type="checkbox" class="form-check-input" id="customDivisions">
                            <label class="form-check-label" for="customDivisions">Назначити каферу і факультет</label>
                        </div>
                    </div>
                </li>

                <li class="row" v-if="data.academic_code">
                    <div class="col-lg-3 list-item list-title">Академічна група:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4 && !data.guid">
                                <input class="item-value" type="text" v-model="data.academic_code">
                        </div>
                        <div v-else>
                            {{data.academic_code}}
                        </div>
                    </div>
                </li>
                <li class="row" v-if="(!data.guid && !data.faculty_code) && (!data.guid && !data.department_code)">
                    <div class="col-lg-3 list-item list-title">Країна:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4 && !data.guid">
                               <Country :data="data"></Country>
                        </div>
                        <div v-else>
                            {{data.country}}
                        </div>
                    </div>
                </li>
                <li class="row" v-if="!data.guid">
                    <div class="col-lg-3 list-item list-title">Входить до списків Forbes та Fortune:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4">
                            <select v-model="data.forbes_fortune">
                                <option value="1">Так</option>
                                <option value="0">Ні</option>
                            </select>
                        </div>
                        <div class="col-lg-9 list-item list-text" v-else>
                            {{data.forbes_fortune ? "Так" : "Ні"}}
                        </div>
                    </div>
                </li>
                <li class="row" v-if="data.guid">
                    <div class="col-lg-3 list-item list-title">5 або більше публікацій у періодичних виданнях Scopus та/або WoS:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4">
                            <select v-model="data.five_publications">
                                <option value="1">Так</option>
                                <option value="0">Ні</option>
                            </select>
                        </div>
                        <div class="col-lg-9 list-item list-text" v-else>
                            {{data.five_publications ? "Так" : "Ні"}}
                        </div>
                    </div>
                </li>
                <li class="row" style="border-bottom: 0">
                    <div class="col-lg-3 list-item list-title">Індекс Гірша:</div>
                    <div class="col-lg-9  list-item list-text d-flex">
                        <div class="col-lg-6 two-col pr-2">
                            <label>БД Scopus</label>
                            <div class="input-container" v-if="authUser.roles_id == 4">
                                <input class="item-value" type="text" v-model="data.scopus_autor_id">
                            </div>
                            <div v-else>
                                {{data.scopus_autor_id}}
                            </div>
                        </div>
                        <div class="col-lg-6 two-col">
                            <label>БД WoS</label>
                            <div class="input-container" v-if="authUser.roles_id == 4">
                                <input class="item-value" type="text" v-model="data.h_index">
                            </div>
                            <div v-else>
                                {{data.h_index}}
                            </div>
                        </div>
                    </div>
                </li>
                <li class="row" style="border-bottom: 0">
                    <div class="col-lg-3 list-item list-title">Індекс Гірша без самоцитувань:</div>
                    <div class="col-lg-9  list-item list-text d-flex">
                        <div class="col-lg-6 two-col pr-2">
                            <label>БД Scopus</label>
                            <div class="input-container" v-if="authUser.roles_id == 4">
                                <input class="item-value" type="text" v-model="data.without_self_citations_scopus">
                            </div>
                            <div v-else>
                                {{data.without_self_citations_scopus}}
                            </div>
                        </div>
                        <div class="col-lg-6 two-col">
                            <label>БД WoS</label>
                            <div class="input-container" v-if="authUser.roles_id == 4">
                                <input class="item-value" type="text" v-model="data.without_self_citations_wos">
                            </div>
                            <div v-else>
                                {{data.without_self_citations_wos}}
                            </div>
                        </div>
                    </div>
                </li>
                <li class="row">
                    <div class="col-lg-3 list-item list-title">Research ID:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4">
                            <input class="item-value" type="text" v-model="data.scopus_researcher_id">
                        </div>
                        <div v-else>
                            {{data.scopus_researcher_id}}
                        </div>
                    </div>
                </li>
                <li class="row">
                    <div class="col-lg-3 list-item list-title">ORCID:</div>
                    <div class="col-lg-9 list-item list-text">
                        <div class="input-container" v-if="authUser.roles_id == 4">
                            <input class="item-value" type="text" v-model="data.orcid">
                        </div>
                        <div v-else>
                            {{data.scopus_researcher_id}}
                        </div>
                    </div>
                </li>
                <li class="row" v-if="data.user">
                    <div class="col-lg-3 list-item list-title">Користувач що зареєстрував автора:</div>
                    <div class="col-lg-9 list-item list-text">
                        <a :href="'/user/'+data.user.id">{{data.user.name}}</a>
                    </div>
                </li>
            </ul>

            <div class="text-right">
                <button type="button" class="export-button my-3" @click="showFilter = !showFilter">Фільтр</button>
            </div>

            <form class="search-block mb-3" v-if="showFilter">
                <div class="form-group">
                    <label>Назва публікації</label>
                    <div class="input-container">
                        <input v-model="filters.title" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label>ПІБ співавтора</label>
                    <div class="input-container">
                        <input type="text" v-model="filters.authors_f">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-lg-4">
                        <label>БД Scopus/WoS</label>
                        <div class="input-container">
                            <select v-model="filters.science_type_id">
                                <option value=""></option>
                                <option value="1">Scopus</option>
                                <option value="2">WoS</option>
                                <option value="3">Scopus та WoS</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Рік видання</label>
                        <div class="input-container">
                            <select v-model="filters.year">
                                <option value=""></option>
                                <option v-for="(item, index) in years" :key="index" :value="item">{{item}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Країна видання</label>
                        <Country :data="filters"></Country>
                    </div>
                </div>
                <div class="form-group">
                    <label>Вид публікації</label>
                    <PublicationTypes :data="filters"></PublicationTypes>
                </div>
                <div class="form-group checkbox col-lg-6">
                    <input v-model="filters.withSupervisor" type="checkbox" class="form-check-input" id="withStudents">
                    <label class="form-check-label" for="withStudents">Під керівництвом</label>
                </div>
                <div class="form-group checkbox col-lg">
                    <input v-model="filters.notPreviousYear" type="checkbox" class="form-check-input" id="notPreviousYear">
                    <label class="form-check-label" for="notPreviousYear">Публікації які не враховані в рейтингу попереднього року</label>
                </div>
                <div class="form-group checkbox col-lg">
                    <input v-model="filters.notThisYear" type="checkbox" class="form-check-input" id="notThisYear">
                    <label class="form-check-label" for="notThisYear">Публікації які не враховані в рейтингу цього року</label>
                </div>
                <SearchButton
                    @click.native="getPublications(1); loadingSearch = true"
                    :disabled="loading || loadingSearch"
                    :loading="loadingSearch"
                    title="Пошук"
                ></SearchButton>
            </form>

            <div class="row my-4" id="header-table">
                <div class="col">
                    <select class="form-control w-50 ml-2" id="sizeTable" v-model="pagination.perPage" @change="getPublications(1)">
                        <option :value="10">10</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                        <option :value="250">250</option>
                        <option :value="500">500</option>
                        <option :value="countPublications">Всі</option>
                    </select>
                </div>
                <div class="col text-right">
                    <paginate
                        class="paginate-top"
                        v-model="pagination.currentPage"
                        :page-count="Math.ceil(countPublications / pagination.perPage)"
                        @click.native="scrollHeader()"

                        prev-text="<"
                        next-text=">"

                        container-class="pagination"
                        page-class="page-item"
                        page-link-class="page-link"
                        prev-class="page-link"
                        next-class="page-link">
                    </paginate>
                </div>
            </div>

            <div class="table-responsive text-center table-list">
                <table :class="['table', 'table-bordered', loading ? 'opacityTable' : '']">
                    <thead>
                        <tr>
                            <td colspan="8" class="bg-white text-left pb-3 pt-0">Всього публікацій: {{ countPublications }}</td>
                        </tr>
                        <tr>
                            <th scope="col">№</th>
                            <th scope="col">Вид публікації</th>
                            <th scope="col">Прізвище та ініціали автора/співавторів</th>
                            <th scope="col">Назва публікації</th>
                            <th scope="col">Рік видання</th>
                            <th scope="col">БД Scopus/WoS</th>
                            <th scope="col">Науковий керівник</th>
                            <th scope="col">Дата занесення</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in publications" :key="index">
                            <td scope="row">{{ pagination.firstItem + index }}</td>
                            <td>{{ item.publication_type.title }}</td>
                            <td>
                                <span class="authors" v-for="(author, index) in item.authors" :key="index">
                                    <a v-if="!author.supervisor" :href="'/user/'+author.author.id">{{author.author.name}} </a>
                                </span>
                            </td>
                            <td><a :href="'/publications/'+item.id"> {{ item.title }} </a> </td>
                            <td>{{ item.year}}</td>
                            <td>{{ item.science_type ? item.science_type.type : '' }}</td>
                            <td>
                                <span class="authors" v-for="(author, index) in item.authors" :key="index">
                                    <a v-if="author.supervisor" :href="'/user/'+author.author.id">{{author.author.name}} </a>
                                </span>
                            </td>
                            <td>{{ item.created_at }}</td>
                        </tr>
                        <tr>
                            <td colspan="8" class="text-left">Всього публікацій: {{ countPublications }} </td>
                        </tr>
                    </tbody>
                </table>
                <div class="spinner-border my-4" role="status" v-if="loading">
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="my-4" v-if="countPublications == 0">
                    Публікації відсутні
                </div>
            </div>

            <paginate
                class="mt-4"
                v-model="pagination.currentPage"
                :page-count="Math.ceil(countPublications / pagination.perPage)"
                @click.native="scrollHeader()"

                prev-text="<"
                next-text=">"

                container-class="pagination"
                page-class="page-item"
                page-link-class="page-link"
                prev-class="page-link"
                next-class="page-link">
            </paginate>

            <div class="step-button-group">
                <back-button></back-button>
                <save-button @click.native="save()" v-if="authUser.roles_id == 4"></save-button>
            </div>
        </div>
    </div>
</template>
<script>
    import years from './mixins/years';
    import divisions from './mixins/divisions';

    import BackButton from "./Buttons/Back";
    import SaveButton from "./Buttons/Save";
    import SearchButton from "./Buttons/SearchButton";
    import Country from "./Forms/Country";
    import PublicationTypes from "./Forms/PublicationTypes";
    export default {
        mixins: [years, divisions],
        data() {
            return {
                countPublications: 0,
                updateLoading: false,
                showFilter: false,
                loadingSearch: false,
                departments2: [],
                data: {
                    name: "",
                    role: {
                        name: ""
                    },
                    guid: null,
                    date_bth: "",
                    job: "",
                    position: "",
                    faculty: "",
                    faculty_code: null,
                    department: "",
                    department_code: null,
                    academic_code: "",
                    country: "",
                    scopus_autor_id: "",
                    h_index: "",
                    scopus_researcher_id: "",
                    orcid: "",
                    five_publications: "0",
                    without_self_citations_wos: "",
                    without_self_citations_scopus: "",
                    created_at: "",
                    custom_divisions: false,
                    user: {
                        id: null,
                        name: ""
                    }
                },
                publications: [],
                pagination: {
                    currentPage: 1,
                    perPage: 10,
                    firstItem: 1
                },
                filters: {
                    title: '',
                    authors_f: '',
                    science_type_id: '',
                    year: '',
                    country: '',
                    publication_type_id: '',
                    faculty_code: '',
                    department_code: '',
                    withSupervisor: false,
                    notPreviousYear: false,
                    notThisYear: false
                },
                loading: true,
                roles: []
            };
        },
        components: {
            BackButton,
            SaveButton,
            SearchButton,
            Country,
            PublicationTypes
        },
        mounted () {
            this.getData();
            this.getPublications(this.pagination.currentPage);
            this.getRoles();
            this.getDepartmentsUser();
        },
        computed: {
            authUser() {
                return this.$store.getters.authUser
            }
        },
        methods: {
            scrollHeader() {
                document.location = '#header-table';
                this.getPublications(this.pagination.currentPage);
            },
            getDepartmentsUser() {
                if(this.data.faculty_code) {
                    this.departments2 = this.divisions.find(item => {
                        return this.data.faculty_code == item.ID_DIV
                    });
                    if(this.departments2) {
                        this.departments2 = this.departments2.departments;
                    }
                } else {
                    this.departments2 = [];
                    this.data.department_code = "";
                }
            },
            updateCabinetInfo() {
                this.updateLoading = true;
                axios.post(`/api/update-cabinet-info/${this.$route.params.id}`)
                .then((response) => {
                    if(response.data.status == 'ok') {
                        this.getData();
                    }
                    if(response.data.status == 'error') {
                        swal.fire({
                            icon: 'error',
                            title: 'В базі даних СумДУ користувача не знайдено'
                        });
                    }
                    this.updateLoading = false;
                }).catch(() => {
                    this.updateLoading = false;
                    swal.fire({
                        icon: 'error',
                        title: 'Помилка'
                    });
                })
            },
            getData() {
                axios.get(`/api/author/${this.$route.params.id}`).then(response => {
                    this.data = response.data;
                    this.getDepartmentsUser();
                    this.loading = false;
                })
            },
            getPublications(page) {
                this.loading = true;
                axios.get('/api/publications/'+this.$route.params.id, {
                    params: {
                        size: this.pagination.perPage,
                        page: page,
                        title: this.filters.title,
                        authors_f: this.filters.authors_f,
                        science_type_id: this.filters.science_type_id,
                        year: this.filters.year,
                        country: this.filters.country,
                        publication_type_id: this.filters.publication_type_id,
                        withSupervisor: this.filters.withSupervisor,
                        notPreviousYear: this.filters.notPreviousYear,
                        notThisYear: this.filters.notThisYear
                    }
                }).then(response => {
                    this.countPublications = response.data.count;
                    this.pagination.currentPage = response.data.currentPage;
                    this.pagination.firstItem = response.data.firstItem;
                    this.publications = response.data.publications.data;
                    this.loading = false;
                    this.loadingSearch = false;
                }).catch(() => {
                    this.loading = false;
                    this.loadingSearch = false;
                })
            },
            getRoles() {
                axios.get('/api/roles').then(response => {
                    this.roles = response.data;
                })
            },
            save() {
                if(!this.data.job) {
                    this.data.job = "Не працює";
                }
                axios.post(`/api/update-author/${this.$route.params.id}`, this.data)
                    .then((response) => {
                        this.getData();
                        swal.fire({
                            icon: 'success',
                            title: 'Інформацію оновлено'
                        });
                }).catch(() => {
                    swal.fire({
                        icon: 'error',
                        title: 'Помилка'
                    });
                })
            }
        },
        watch: {
            $route(to, from) {
                this.data.publications = [];
                this.getData();
            }
        }
    }
</script>
<style lang="css" scoped>
    .opacityTable {
        opacity: 0.5;
    }
    .paginate-top.pagination {
        margin: 0;
        float: right;
    }
    .checkbox {
        padding: 0;
    }
    .checkbox input[type=checkbox] {
        width: 20px;
        height: 20px;
        margin: 0;
    }
    .checkbox label {
        margin-left: 40px;
    }
    .form-group {
        margin-bottom: 10px;
    }
    .table-list {
        margin-top: 10px;
    }
    img.update-cabinet-info {
        cursor: pointer;
    }
    .spin {
        animation: 1s linear 0s normal none infinite running spin;
        -webkit-animation: 1s linear 0s normal none infinite running spin;
    }
    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    @-webkit-keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
