<template>
    <div>
        <div class="step-content">
            <div class="form-group">
                <label class="item-title">Назва конференції *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.name_conference">
                    <div class="hint"><span>Вказати повну назву конференції</span></div>
                </div>
                <div class="error" v-if="$v.publicationData.name_conference.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
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
                <label class="item-title">Видавництво</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.editor_name">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Том</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.number">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Сторінки *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.pages">
                </div>
                <div class="error" v-if="$v.publicationData.pages.$error">
                    Поле обов'язкове для заповнення
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
                <label class="item-title">Місто видання</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.city">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">DOI</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.doi">
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
                name_conference: {
                    required
                },
                year: {
                    required
                },
                country: {
                    required
                },
                pages: {
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
