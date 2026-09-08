import { useTrans } from '@/hooks/use-trans';

export function Faq() {
    const { t } = useTrans();

    const faqData = [
        {
            question: t('Wat doen wij precies?'),
            answer: t('Wij hebben een programma dat automatisch beschikbare examenplekken monitort en jou direct kan inschrijven zodra er een plek vrijkomt. Voor slechts €35 regelen wij een examenplek voor jou, meestal binnen 2 weken in plaats van de gebruikelijke 6 maanden wachttijd. Belangrijk: wij zijn de eerste die informatie ontvangen over een nieuwe plek, maar als er geen nieuwe plek vrijkomt kunnen wij daar natuurlijk niets aan doen. Volgens onze data komen er regelmatig plekken vrij en wij hebben nog nooit een probleem gehad om binnen 2 weken een plek te vinden.'),
        },
        {
            question: t('Belangrijk!'),
            answer: t('Als u met uw rijschool rijdt, contacteer dan uw rijschool. Indien u een voertuig van een rijschool gebruikt moet dit via uw desbetreffende rijschool geregeld worden. Gelieve hen te contacteren zodat zij dit in orde kunnen brengen. Wij kunnen u hierbij niet helpen!'),
        },
        {
            question: t('Welke regios supporten wij?'),
            answer: t('Wij ondersteunen momenteel de volgende examencentra: Sint-Denijs-Westrem, Brakel, Eeklo, Erembodegem en Sint-Niklaas.')
        },
        {
            question: t('Welke gegevens bewaren jullie en waarom?'),
            answer: t('Wij bewaren enkel de gegevens die noodzakelijk zijn om u in te schrijven voor een rijexamen, zoals uw naam, e-mailadres en eventueel de gegevens die vereist zijn door het examencentrum (bv. rijksregisternummer, geboortedatum, adres). Deze gegevens worden uitsluitend gebruikt om uw boeking uit te voeren en worden niet verkocht of gedeeld met derden.'),
        },
        {
            question: t('Hoe lang worden mijn gegevens bewaard?'),
            answer: t('Uw gegevens worden bewaard zolang uw account actief is. U kunt op elk moment uw account en alle bijbehorende gegevens laten verwijderen via de instellingen of door contact op te nemen via rijbewijsboeker@gmail.be.'),
        },
        {
            question: t('Wat zijn mijn rechten met betrekking tot mijn gegevens?'),
            answer: t('Conform de GDPR heeft u recht op inzage, wijziging, verwijdering en overdracht van uw persoonsgegevens. Ook heeft u het recht om bezwaar te maken tegen de verwerking of een beperking van de verwerking te vragen. U kunt deze rechten uitoefenen door contact op te nemen via rijbewijsboeker@gmail.be.'),
        },
        {
            question: t('Zijn jullie verbonden aan SBAT of een examencentrum?'),
            answer: t('Nee, Rijbewijsboeker is op geen enkele manier verbonden aan, geassocieerd met of onderdeel van SBAT nv of enig ander examencentrum. Wij zijn een onafhankelijke dienst die u helpt bij het vinden en boeken van beschikbare examendata.'),
        },
    ];

    return (
        <div id="faq" className="mx-auto max-w-2xl py-16">
            <h2 className="mb-10 text-center text-3xl font-bold tracking-tight text-white sm:text-4xl">{t('Veelgestelde Vragen')}</h2>
            <div className="divide-y divide-gray-600">
                {faqData.map((faq, index) => (
                    <div key={faq.question} className={index > 0 ? 'pt-8' : ''}>
                        <dt className="text-2xl leading-7 font-semibold text-white">{faq.question}</dt>
                        <dd className="mt-2 text-base leading-7 text-white">{faq.answer}</dd>
                    </div>
                ))}
            </div>
        </div>
    );
}
