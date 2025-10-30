export async function getIntervention(numInt) {
    const res = await fetch(`/api/intervention/${numInt}`);
    return res.json();
}

export async function getRdvs(date) {
    const res = await fetch(`/api/rdv?date=${date}`);
    return res.json();
}

export async function reassignRdv(rdv, newTech, csrfToken) {
    const res = await fetch(`/api/rdv/${rdv.num}/reassign`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({technicien: newTech})
    });
    return res.json();
}
