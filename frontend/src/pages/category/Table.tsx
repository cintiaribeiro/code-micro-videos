import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import React , {useState, useEffect}from 'react';
import { httpVideo } from '../../util/http';

const columsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome"
    },
    {
        name: "is_active",
        label: "Ativo?"
    },
    {
        name: "created_at",
        label: "Criado em"
    }

];
const data = [
    { name: "Teste1", is_active: true, created_at: "2020-09-10"},
    { name: "Teste2", is_active: false, created_at: "2020-09-10"},
    { name: "Teste3", is_active: true, created_at: "2020-09-10"},
    { name: "Teste4", is_active: false, created_at: "2020-09-10"},
]

type Props = {

};
const Table = (props: Props) => {

    const [data, setData ] = useState([]);
    
    useEffect(()=>{
        httpVideo.get('categories').then(
            response =>  setData(response.data.date)
        )
    }, []);

    return (
        <div>
            <MUIDataTable 
                title="Listagem de categorias"
                columns={columsDefinition}
                data={data}
            />
        </div>
    );
}

export default Table;