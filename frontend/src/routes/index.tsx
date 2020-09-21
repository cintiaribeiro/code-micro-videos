import { RouteProps } from 'react-router-dom';
import Dashboard from '../pages/Dashboard';
import CategoryList from '../pages/category/PageList';
import MemberList from '../pages/cast-member/PageList';
import GenreList from '../pages/genres/PageList';

export interface MyRouteProps extends RouteProps{
    name:string;
    label:string;
}
const routes: MyRouteProps[] = [
    {
        name: "dashboar",
        label: "Dashboard",
        path: "/",
        component: Dashboard,
        exact: true
    },
    {
        name: "categories.list",
        label: "Listar categorias",
        path: "/categories",
        component: CategoryList,
        exact: true
    },
    {
        name: "categories.create",
        label: "Criar categorias",
        path: "/categories/create",
        component: CategoryList,
        exact: true
    },
    {
        name: "cast_members.list",
        label: "Listar membros de elenco",
        path: "/cast-members",
        component: MemberList,
        exact: true
    },
    {
        name: "cast_members.create",
        label: "Criar membros",
        path: "/cast-members/create",
        component: MemberList,
        exact: true 
    },
    {
        name: "genres.list",
        label: "Listar generos",
        path: "/genres",
        component: GenreList,
        exact: true
    },
    {
        name: "genres.create",
        label: "Criar generos",
        path: "/genres/create",
        component: GenreList,
        exact: true
    },
];

export default routes;