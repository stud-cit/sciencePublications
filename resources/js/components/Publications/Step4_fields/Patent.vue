<template>
    <div>
        <div class="step-content">
            <div class="form-group">
                <label class="item-title">№ патенту *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.number_certificate">
                </div>
                <div class="error" v-if="$v.publicationData.number_certificate.$error">
                    Введіть лише цифри
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Країна, де отриманий патент *</label>
                <Country :data="publicationData"></Country>
                <div class="error" v-if="$v.publicationData.country.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">МПК *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.mpk">
                </div>
                <div class="error" v-if="$v.publicationData.mpk.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Власник майнових прав *</label>
                <div class="input-container">
                    <select class="item-value" v-model="applicant_id" @change="clearApplicant()">
                        <option :value="true">СумДУ</option>
                        <option :value="false">Не СумДУ</option>
                    </select>
                </div>
            </div>
            <div class="form-group" v-if="!applicant_id">
                <label class="item-title">Вкажіть власника майнових прав *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.applicant">
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">№ заявки *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.application_number">
                </div>
                <div class="error" v-if="$v.publicationData.application_number.$error">
                    Введіть лише цифри
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Дата подачі заявки *</label>
                <div class="input-container">
                    <date-picker
                        v-model="publicationData.date_application"
                        value-type="YYYY-MM-DD"
                        :lang="datepicker.lang"
                        :editable="false"
                        :popup-style="datepicker.styles"
                    ></date-picker>
                    <input style="display:none" class="item-value" type="text" v-model="publicationData.date_application" required>
                </div>
                <div class="error" v-if="$v.publicationData.date_application.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Дата публікації про видачу *</label>
                <div class="input-container">
                    <date-picker
                        v-model="publicationData.date_publication"
                        value-type="YYYY-MM-DD"
                        :lang="datepicker.lang"
                        :editable="false"
                        :popup-style="datepicker.styles"
                    ></date-picker>
                    <input style="display:none" class="item-value" type="text" v-model="publicationData.date_publication" required>
                </div>
                <div class="error" v-if="$v.publicationData.date_publication.$error">
                    Поле обов'язкове для заповнення
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">№ бюлетеня *</label>
                <div class="input-container">
                    <input class="item-value" type="text" v-model="publicationData.newsletter_number">
                </div>
                <div class="error" v-if="$v.publicationData.newsletter_number.$error">
                    Введіть лише цифри
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Комерціалізовано університетом *</label>
                <div class="input-container hint-container">
                    <select class="item-value" v-model="publicationData.commerc_university">
                        <option value="1">Так </option>
                        <option value="0">Ні </option>
                    </select>
                    <div class="hint" ><span>"так" -у разі надходження коштів на рахунок університету</span></div>
                </div>
            </div>
            <div class="form-group">
                <label class="item-title">Комерціалізовано штатними співробітниками університету *</label>
                <div class="input-container hint-container">
                    <select class="item-value" v-model="publicationData.commerc_employees">
                        <option value="1">Так </option>
                        <option value="0">Ні </option>
                    </select>
                    <div class="hint" ><span>"так" - при отриманні коштів автором - штатним співробітником університету</span></div>
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
    import DatePicker from 'vue2-datepicker';
    import 'vue2-datepicker/index.css';
    import {required, requiredIf} from "vuelidate/lib/validators";

    export default {
        data() {
            return {
                applicant_id: true,
                datepicker: {
                    lang: {
                        formatLocale: {
                            months: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
                            monthsShort: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
                            weekdays: ["Неділя", "Понеділок", "Вівторок", "Середа", "Четвер", "П'ятниця", "Субота"],
                            weekdaysShort: ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'НД'],
                            weekdaysMin: ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'НД']
                        }
                    },
                    styles: { left: '30%' }
                },
            }
        },
        props: {
            publicationData: Object
        },
        components: {
            DatePicker,
            CloseEditButton,
            Country
        },
        validations: {
            publicationData: {
                number_certificate: {
                    required,
                },
                mpk: {
                    required
                },
                application_number: {
                    required,
                },
                date_publication: {
                    required
                },
                date_application: {
                    required
                },
                newsletter_number: {
                    required
                },
                country: {
                    required
                },
            },
        },
        created() {
            if(this.publicationData.applicant && this.publicationData.applicant != 'СумДУ') {
                this.applicant_id = false;
            }
        },
        methods: {
            clearApplicant() {
                this.publicationData.applicant = "";
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
                this.publicationData.year = this.publicationData.date_publication.slice(0,4);
                this.publicationData.applicant = this.applicant_id ? 'СумДУ' : this.publicationData.applicant;
                this.$parent.$emit('getData', 4);
            },
            prevStep(){
                this.$parent.$emit('prevStep');
            },
        }
    }
</script>

<style lang="scss" scoped>
    .mx-datepicker {
        width: 100%;
    }
</style>
