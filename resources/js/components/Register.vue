<template>
    <div class="container page-content general-block">
        <h1 class="page-title">{{ name }}</h1>
        <h2 class="subtitle">Вітаємо Вас на сервісі "Наукові публікації". Заповніть наступні поля, у разі їх наявності, та натисніть кнопку "Продовжити".</h2>
        <form class="search-block">
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Індекс Гірша Scopus</label>
                    <div class="input-container">
                        <input class="item-value" type="text" v-model="data.scopus_autor_id">
                    </div>
                </div>
                <div class="form-group col-6">
                    <label>Індекс Гірша WoS</label>
                    <div class="input-container">
                        <input class="item-value" type="text" v-model="data.h_index">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Індекс Гірша Scopus без самоцитувань</label>
                    <div class="input-container">
                        <input class="item-value" type="text" v-model="data.without_self_citations_scopus">
                    </div>
                </div>
                <div class="form-group col-6">
                    <label>Індекс Гірша WoS без самоцитувань</label>
                    <div class="input-container">
                        <input class="item-value" type="text" v-model="data.without_self_citations_wos">
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Research ID</label>
                    <div class="input-container">
                        <input class="item-value" type="text" v-model="data.scopus_researcher_id">
                    </div>
                </div>
                <div class="form-group col-6">
                    <label>ORCID</label>
                    <div class="input-container">
                        <input class="item-value" type="text" v-model="data.orcid">
                    </div>
                </div>
            </div>
        </form>
        <div class="step-button-group">
            <button @click="save()" class="next" :disabled="loading">
                <span
                    class="spinner-border spinner-border-sm"
                    style="width: 25px; height: 25px"
                    role="status"
                    aria-hidden="true"
                    v-if="loading"
                ></span>
                <span class="sr-only" v-if="loading">Loading...</span>
                Продовжити
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                loading: false,
                name: "",
                data: {
                    h_index: "",
                    scopus_autor_id: "",
                    scopus_researcher_id: "",
                    orcid: "",
                    without_self_citations_scopus: "",
                    without_self_citations_wos: ""
                }
            };
        },
        created() {
            this.getName();
        },
        methods: {
            getName() {
                this.name = this.$store.getters.authUser.surname + " " + this.$store.getters.authUser.name + " " + this.$store.getters.authUser.patronymic
            },
            save() {
                this.loading = true;
                axios.post('/api/register', this.data)
                    .then((response) => {
                        this.$store.dispatch('setUser', response.data)
                        this.$router.push('/home');
                    }).catch(() => {
                        this.loading = false;
                    })
            }
        },
    }
</script>
<style lang="scss" scoped>
        .search-block {
            margin-top: 30px;
        }
        h2.subtitle {
            font-size: 22px;
            text-align: center;
            margin-top: 20px;
            border-bottom: 0px;
        }
        form{
            margin-top: 60px;
        }
        .save-btn{
            margin-top: 50px;
            padding: 27px 85px;
            font-family: Arial;
            font-style: normal;
            font-weight: normal;
            font-size: 30px;
            line-height: 34px;
            text-align: center;
            color: #FFFFFF;
            background: #7EF583;
            border-radius: 6px;

        }

        @media (max-width: 575px) {

            form{
                margin-top: 30px;
            }

            .save-btn{
                margin-top: 25px;
                padding: 15px 40px;
                font-size: 20px;
                line-height: 24px;
                border-radius: 6px;

            }

        }


</style>
