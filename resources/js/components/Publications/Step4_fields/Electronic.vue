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
                <div class="error" v-if="$v.publicationData.year.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Кількість томів </label>
                <div class="input-container">
                    <input class="item-value" type="number" v-model="publicationData.number_volumes">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Том </label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.number">
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
                <label class="item-title">Електронна адреса (url) *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.url">
                </div>
                <div class="error" v-if="$v.publicationData.url.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Режим доступу *</label>
                <div class="input-container">
                    <select class="item-value" v-model="publicationData.access_mode">
                        <option value="1">Відкритий </option>
                        <option value="0">Закритий </option>
                    </select>
                </div>
                <div class="error" v-if="$v.publicationData.access_mode.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
        </div>
        <div class="step-button-group">
            <button class="prev" @click="prevStep">На попередній крок</button>
            <button class="next active" @click="nextStep">Продовжити</button>
            <close-edit-button v-if="$route.name == 'publications-edit'"></close-edit-button>
        </div>
    </div>
</template>

<script>
    import Country from "../../Forms/Country";
    import CloseEditButton from "../../Buttons/CloseEdit";
    import years from '../../mixins/years';
    import {required} from "vuelidate/lib/validators";

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
                url: {
                    required
                },
                year: {
                    required
                },
                country: {
                    required
                },
                access_mode: {
                    required
                }
            }
        },
        methods: {
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