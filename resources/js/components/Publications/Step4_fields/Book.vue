<template>
    <div>
        <div class="step-content">
            <div class="form-group">
                <label class="item-title">Рік видання *</label>
                <div class="input-container">
                    <select class="item-value" v-model="publicationData.year">
                        <option v-for="(year, index) in years" :key="index" :value="year">{{ year }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">За редакцією (у родовому відмінку)</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.by_editing">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Країна видання *</label>
                <Country :data="publicationData"></Country>
                <div class="error" v-if="$v.publicationData.country.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Місто видання </label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.city">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Назва редакції </label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.editor_name">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Кількість сторінок *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.pages">
                </div>
                <div class="error" v-if="$v.publicationData.pages.$error">
                    Неправильно введені дані
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Кількість томів</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.number_volumes">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Том</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.number">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">DOI </label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.doi">
                </div>
            </div>
        </div>
        <div class="step-button-group">
            <button class="prev" @click="prevStep">На попередній крок</button>
            <button class="next active" @click="nextStep">Продовжити </button>
            <close-edit-button v-if="$route.name == 'publications-edit'"></close-edit-button>
        </div>
    </div>
</template>

<script>
    import {required} from "vuelidate/lib/validators";
    import Country from "../../Forms/Country";
    import CloseEditButton from "../../Buttons/CloseEdit";
    import years from '../../mixins/years';
    export default {
        mixins: [years],
        props: {
            publicationData: Object
        },
        components: {
            CloseEditButton,
            Country
        },
        validations: {
            publicationData: {
                pages: {
                    required,
                    validFormat: val => /^([^a-za-zа-яіїєё]+)$/.test(val), 
                },
                year: {
                    required
                },
                country: {
                    required
                }
            },
        },
        methods: {
            getCountry() {
                axios.get('/api/country').then(response => {
                    this.country = response.data;
                })
            },
            nextStep() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    swal.fire({
                        icon: 'error',
                        title: 'Не всі поля заповнено!'
                    })
                    return
                }
                this.$parent.$emit('getData', 4);
            },
            prevStep(){
                this.$parent.$emit('prevStep');
            }
        }
    }
</script>